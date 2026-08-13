<?php

namespace App\Support;

use App\Models\Company;

/**
 * The company's identity, for the parts of the interface that are not a
 * document: the browser tab, the sign-in screen, the sidebar mark.
 *
 * The company record is the source of truth; config('app.name') is only the
 * fallback for a fresh install that has not run through /install yet.
 */
class Brand
{
    protected static ?Company $company = null;
    protected static bool $resolved = false;

    public static function company(): ?Company
    {
        if (! self::$resolved) {
            self::$company = Company::query()->first();
            self::$resolved = true;
        }

        return self::$company;
    }

    public static function name(): string
    {
        return self::company()?->name ?: config('app.name', 'Invoicing');
    }

    public static function logoUrl(): ?string
    {
        $path = self::company()?->logo_path;

        return $path ? asset('storage/' . $path) : null;
    }

    /**
     * The initials shown when no logo has been uploaded.
     */
    public static function initials(): string
    {
        $words = preg_split('/\s+/', trim(self::name())) ?: [];
        $letters = array_map(fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2));

        return mb_strtoupper(implode('', $letters)) ?: 'IN';
    }

    /**
     * "Invoices · The Higglers Company", or just the company on a bare page.
     */
    public static function title(?string $page = null): string
    {
        return filled($page) ? $page . ' · ' . self::name() : self::name();
    }

    /**
     * A sensible page title derived from the current route, so every screen
     * gets a real tab title without each one having to declare it.
     */
    public static function titleForRoute(): ?string
    {
        $name = optional(request()->route())->getName();

        if (! $name) {
            return null;
        }

        $map = [
            'dashboard' => 'Dashboard',
            'invoices.index' => 'Invoices',
            'invoices.create' => 'New Invoice',
            'invoices.edit' => 'Edit Invoice',
            'invoices.show' => 'Invoice',
            'quotations.index' => 'Quotations',
            'quotations.create' => 'New Quotation',
            'quotations.edit' => 'Edit Quotation',
            'quotations.show' => 'Quotation',
            'clients.index' => 'Clients',
            'clients.create' => 'New Client',
            'clients.edit' => 'Edit Client',
            'clients.show' => 'Client',
            'clients.user.create' => 'New Client User',
            'settings.show' => 'Settings',
            'profile.edit' => 'Profile',
            'login' => 'Sign in',
            'register' => 'Create account',
            'password.request' => 'Reset password',
            'password.reset' => 'Choose a new password',
            'verification.notice' => 'Verify email',
            'password.confirm' => 'Confirm password',
            'install.index' => 'Set up',
        ];

        return $map[$name] ?? null;
    }
}
