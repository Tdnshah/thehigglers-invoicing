<?php

namespace App\Services;

use App\Models\Client;
use App\Models\Company;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Builds invoice and quotation numbers from a company defined format.
 *
 * A format is literal text with tokens in braces, e.g.
 *
 *   THC/{FY}/{SEQ:4}            -> THC/2026-27/0001
 *   INV-{CLIENT}-{YYYY}-{MM}-{SEQ:2} -> INV-CLI1-2026-08-01
 *
 * The running counter is scoped to everything in the number except the
 * sequence itself, so the format decides what the series resets on: put {FY}
 * in it and the series restarts every April, put {CLIENT} in it and each
 * client gets its own run.
 *
 * Rule 46 of the CGST Rules caps a tax invoice number at 16 characters and
 * allows only letters, digits, hyphen and slash, so a rendered number is
 * validated against both before it is handed out.
 */
class DocumentNumberGenerator
{
    public const INVOICE = 'invoice';
    public const QUOTATION = 'quotation';

    public const DEFAULT_INVOICE_FORMAT = 'INV/{FY}/{SEQ:4}';
    public const DEFAULT_QUOTATION_FORMAT = 'QT/{FY}/{SEQ:4}';

    /**
     * GST allows A-Z, a-z, 0-9, hyphen and slash only, to a maximum of 16.
     */
    public const MAX_LENGTH = 16;
    public const ALLOWED_PATTERN = '/^[A-Za-z0-9\/-]+$/';

    /**
     * The tokens a format may use, with the label shown in company settings.
     * Friendly aliases are resolved to these in normaliseToken().
     */
    public const TOKENS = [
        'FY' => 'Financial year, e.g. 2026-27 (April to March)',
        'FY_SHORT' => 'Short financial year, e.g. 26-27',
        'YYYY' => 'Calendar year, e.g. 2026',
        'YY' => 'Short calendar year, e.g. 26',
        'MM' => 'Month, 01 to 12',
        'DD' => 'Day of month, 01 to 31',
        'CLIENT' => 'Client code, e.g. CLI1',
        'SEQ' => 'Running number. Pad it with {SEQ:4} for 0001',
    ];

    /**
     * Reserve and render the next number for a document.
     */
    public function next(string $type, Company $company, ?Client $client = null, ?CarbonInterface $date = null): string
    {
        $format = $this->formatFor($type, $company);
        $date = $date ? Carbon::instance($date->toDateTime()) : Carbon::now();

        $scopeKey = $this->render($format, $date, $client, null);
        $sequence = $this->reserve($type, $company, $scopeKey);

        return $this->render($format, $date, $client, $sequence);
    }

    /**
     * Render a format without touching any counter, for the settings preview.
     */
    public function preview(string $format, ?Client $client = null, int $sequence = 1, ?CarbonInterface $date = null): string
    {
        $date = $date ? Carbon::instance($date->toDateTime()) : Carbon::now();

        return $this->render($format, $date, $client, $sequence);
    }

    /**
     * Why a format is unusable, or null when it is fine.
     */
    public function validationError(?string $format): ?string
    {
        if (! filled($format)) {
            return null; // Falls back to the default format.
        }

        foreach ($this->tokensIn($format) as $token) {
            if (! array_key_exists($this->normaliseToken($token), self::TOKENS)) {
                return 'Unknown token {' . $token . '}. Available tokens: ' . implode(', ', array_keys(self::TOKENS)) . '.';
            }
        }

        if (! str_contains(strtoupper(str_replace(' ', '_', $format)), '{SEQ')
            && ! str_contains(strtoupper($format), '{COUNT')
            && ! str_contains(strtoupper($format), '{NUMBER')) {
            return 'The format needs a {SEQ} token, otherwise every document would get the same number.';
        }

        // Worst case sample: a long client code and a four digit sequence.
        $sample = $this->preview($format, null, 9999);

        if (! preg_match(self::ALLOWED_PATTERN, $sample)) {
            return 'A GST invoice number may only contain letters, digits, hyphen and slash. This format produces "' . $sample . '".';
        }

        if (mb_strlen($sample) > self::MAX_LENGTH) {
            return 'A GST invoice number may be at most ' . self::MAX_LENGTH . ' characters. This format produces "' . $sample . '" (' . mb_strlen($sample) . ').';
        }

        return null;
    }

    public function formatFor(string $type, Company $company): string
    {
        $configured = $type === self::INVOICE
            ? $company->invoice_number_format
            : $company->quotation_number_format;

        return filled($configured)
            ? $configured
            : ($type === self::INVOICE ? self::DEFAULT_INVOICE_FORMAT : self::DEFAULT_QUOTATION_FORMAT);
    }

    /**
     * The Indian financial year runs April to March, so anything before April
     * belongs to the year that started the previous April.
     */
    public function financialYear(CarbonInterface $date): array
    {
        $startYear = $date->month >= 4 ? $date->year : $date->year - 1;

        return [$startYear, $startYear + 1];
    }

    /**
     * Take the next number in a scope under a row lock, so two documents
     * created at the same moment cannot land on the same serial.
     */
    protected function reserve(string $type, Company $company, string $scopeKey): int
    {
        return DB::transaction(function () use ($type, $company, $scopeKey) {
            $row = DB::table('document_sequences')
                ->where('company_id', $company->id)
                ->where('document_type', $type)
                ->where('scope_key', $scopeKey)
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('document_sequences')->insert([
                    'company_id' => $company->id,
                    'document_type' => $type,
                    'scope_key' => $scopeKey,
                    'next_number' => 2,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return 1;
            }

            DB::table('document_sequences')
                ->where('id', $row->id)
                ->update(['next_number' => $row->next_number + 1, 'updated_at' => now()]);

            return $row->next_number;
        });
    }

    /**
     * Replace every token. A null sequence blanks the sequence token, which is
     * how the scope key for the counter is built.
     */
    protected function render(string $format, CarbonInterface $date, ?Client $client, ?int $sequence): string
    {
        [$fyStart, $fyEnd] = $this->financialYear($date);

        return preg_replace_callback('/\{([^}]+)\}/', function ($matches) use ($date, $client, $sequence, $fyStart, $fyEnd) {
            $raw = $matches[1];
            $token = $this->normaliseToken($raw);
            $pad = $this->padWidth($raw);

            return match ($token) {
                'FY' => $fyStart . '-' . substr((string) $fyEnd, -2),
                'FY_SHORT' => substr((string) $fyStart, -2) . '-' . substr((string) $fyEnd, -2),
                'YYYY' => (string) $date->year,
                'YY' => $date->format('y'),
                'MM' => $date->format('m'),
                'DD' => $date->format('d'),
                'CLIENT' => $this->clientCode($client),
                'SEQ' => $sequence === null ? '' : str_pad((string) $sequence, $pad, '0', STR_PAD_LEFT),
                default => $matches[0],
            };
        }, $format);
    }

    /**
     * Accepts {SEQ}, {seq:4}, {Count} and {Client ID} equally, so a format
     * typed the obvious way works.
     */
    protected function normaliseToken(string $raw): string
    {
        $name = strtoupper(trim(explode(':', $raw, 2)[0]));
        $name = str_replace([' ', '-'], '_', $name);

        return match ($name) {
            'COUNT', 'NUMBER', 'SERIAL', 'SEQUENCE' => 'SEQ',
            'CLIENT_ID', 'CLIENT_CODE', 'CUSTOMER' => 'CLIENT',
            'YEAR' => 'YYYY',
            'MONTH' => 'MM',
            'DAY' => 'DD',
            'FINANCIAL_YEAR' => 'FY',
            default => $name,
        };
    }

    protected function padWidth(string $raw): int
    {
        $parts = explode(':', $raw, 2);

        return isset($parts[1]) && ctype_digit(trim($parts[1])) ? (int) trim($parts[1]) : 1;
    }

    /**
     * A client's own code when it has one, otherwise a stable fallback so a
     * format using {CLIENT} still produces a unique, valid number.
     */
    protected function clientCode(?Client $client): string
    {
        if (! $client) {
            return 'CLI';
        }

        return filled($client->code)
            ? preg_replace('/[^A-Za-z0-9]/', '', $client->code)
            : 'CLI' . $client->id;
    }

    /**
     * @return array<int, string>
     */
    protected function tokensIn(string $format): array
    {
        preg_match_all('/\{([^}]+)\}/', $format, $matches);

        return $matches[1] ?? [];
    }
}
