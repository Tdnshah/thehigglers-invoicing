# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

**Primary: the company admin.** The operator who issues the money documents, currently The Higglers' own team. Identified in code by `isCompanyAdmin()` (a `company_id` on the user). They create and revise quotations, chase approval, clone approved quotations into invoices, record payments, and produce the print/PDF a client receives. Effectively the entire current UI is built for this role.

**Downstream reader: the accountant or CA.** Never logs in, but consumes the issued documents for GST filing. A confirmed constraint on formats, numbering, and auditability.

**Present in code, not the current focus: the client user.** Identified by `isClientUser()` (a `client_id`), with read access scoped to their own client record. The product today is described by its owner as majorly an invoice creation and management platform, so this portal is not the surface being designed for. Whether it becomes a supported surface is undecided.

## Product Purpose

Create, manage, and issue Indian GST compliant quotations and tax invoices, track payments against them, and produce the printed and PDF documents that go to clients and to the accountant.

Success is that a document is a valid tax invoice with no manual correction, that the quotation to invoice to payment cycle is recorded rather than remembered, and that filing season needs no reconstruction.

The stated intent is to develop this into a SaaS product. It is not one yet.

## Positioning

GST compliance is the mechanism, not a feature bolted onto a generic invoicing tool: supply type drives the tax split (CGST plus SGST for intra-state, IGST for inter-state, zero-rated for exports under a LUT), place of supply and HSN/SAC are first-class fields, and multi-currency export billing sits alongside domestic INR billing.

The intended differentiator is the destination: automated GST filing, at minimum GSTR-1 and GSTR-3B, generated from the same records that produced the invoices. A general invoicing tool cannot truthfully claim that.

## Operating Context

**The document lifecycle.** Quotation (draft, sent, approved or rejected) with a revision tree, one active revision, and the tree locking once a version is approved. An approved quotation is cloned into an invoice, and every approved quotation is expected to end up with a related invoice. The invoice runs draft, sent, paid, overdue, with a payments ledger that marks it paid once recorded payments reach the total. Paid invoices are locked from editing.

**Documents.** Quotations and invoices render through one shared template pipeline (`App\Support\Documents\DocumentData` into `resources/views/documents/templates/`), producing both the on-screen print view and the Dompdf PDF, so the two can never drift. Creating an invoice emails the client when an email address exists.

**Internal record keeping.** Both quotations and invoices carry an internal notes thread, used across the approval and payment cycle. It is team-only and never appears on a client document, a print view, or a PDF.

**Company identity.** Company settings hold what appears on every document: name, address, GSTIN, logo, bank details, and custom fields. Custom fields currently hold LUT registrations per financial year, selectable per document.

**Environment.** Self-hosted. Docker Compose with nginx, PHP-FPM, and MariaDB, plus an `/install` first-run flow.

## Capabilities and Constraints

- Stack: Laravel 12 on PHP 8.2, Blade with Tailwind and Alpine, Vite, `barryvdh/laravel-dompdf`, MariaDB, Breeze auth.
- Roles are implicit rather than a permission package: a `company_id` means admin, a `client_id` means client user. Access is enforced by per-controller checks, and access leaks are the standing risk.
- Supply types: regular (CGST and SGST), interstate (IGST), export (zero-rated, quoted under a LUT).
- Currencies: INR, USD, EUR, GBP.
- GST state codes follow the full CBIC list. Retired codes still resolve for historical documents but are not offered on new ones.
- Invoices have no field-level revision history. Only creation and last-modified timestamps exist.

**Hard constraints, confirmed.** Indian GST compliance on every issued document. Predictable, auditable output for the accountant or CA handoff. Multi-currency export invoicing including zero-rated LUT treatment. Self-hosted operation with no dependency on paid external services for core invoicing, PDF generation, or storage.

**Planned.** Automated GST filing, at least GSTR-1 and GSTR-3B.

**Explicitly undecided.** The SaaS tenancy model (today data is scoped per `user_id` against a single company record and an `/install` flow, which is not real multi-tenancy). The product name and identity for the SaaS offering. Pricing. The hosting or deployment target. Whether the client portal becomes a supported surface.

## Brand Commitments

The operating company is The Higglers. Its logo is uploaded through company settings and, when present, replaces the text company name on documents.

No name, identity, or brand has been chosen for the SaaS product itself. Do not invent one.

## Evidence on Hand

- A real company record with a GSTIN and two LUT registrations held per financial year.
- A company logo asset stored under the public storage disk.
- A small amount of real production data: an issued invoice with payments recorded, and an approved quotation already cloned to an invoice.
- No testimonials, customers, case studies, benchmarks, pricing, press, or usage figures exist. Do not fabricate any of them.

## Product Principles

1. **Compliance is the product.** A document that is not a valid Indian tax invoice is a defect, never a style choice.
2. **Design for the audit, not just the send.** Numbering, tax splits, and document contents have to be explainable to a CA months later, by someone who was not in the room.
3. **Internal stays internal.** Private notes, workflow state, and team context never reach a client document.
4. **Single-company assumptions are debt.** The trajectory is SaaS, so avoid hard-coding this company's specifics into logic or copy.
5. **The destination is filing.** Data captured today should be shaped so GSTR-1 and GSTR-3B can be produced from it without re-entry.
