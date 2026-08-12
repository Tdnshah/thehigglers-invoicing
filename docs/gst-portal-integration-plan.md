# GST portal integration: research and plan

Researched 12 August 2026. Everything here is from public sources listed at the
bottom; nothing is inferred from prior knowledge without a source. Rules and
thresholds change, so re-verify before building.

**Status: research and plan only. Nothing has been implemented.**

---

## 1. What "integrating with the GST portal" actually means

It is three separate systems, not one. They have different APIs, different
onboarding, and different urgency for this product.

| System | What it does | Who runs it | Relevance |
|---|---|---|---|
| **IRP** (Invoice Registration Portal) | Registers an invoice in real time, returns an IRN and a signed QR code | NIC and other IRPs | The invoice is not GST-valid without it, once you cross the threshold |
| **GSTN return APIs** | GSTR-1 (outward supplies), GSTR-3B (summary), GSTR-2B / IMS (inward) | GSTN via GSPs | The monthly filing you asked to automate |
| **E-way bill** | Movement of goods above ₹50,000 | NIC | Not relevant: this product invoices services |

The important sequencing point: **e-invoicing feeds GSTR-1 automatically.** Once
invoices carry an IRN, their data auto-populates GSTR-1, so building IRP
integration first makes the return work substantially smaller. Build in that
order.

---

## 2. The access constraint that shapes everything

You cannot call GSTN or the IRP directly as a small taxpayer.

- Direct IRP API access is available only to taxpayers with turnover above
  **₹500 crore**, subject to technical pre-requisites. Everyone else must connect
  through a **GSP** (GST Suvidha Provider) or an already-integrated ERP.
- Direct integration, where permitted, is reported at roughly **₹5 lakh** setup
  plus recurring costs.
- A GSP is a company authorised by GSTN to operate middleware between a
  taxpayer's software and the portal. You integrate against the GSP's gateway
  (typically OAuth 2.0 plus GSTIN credentials) and the GSP talks to GSTN.

**Consequence for this product:** we are an **ASP** (Application Service
Provider). We build the application, and we contract a GSP for the pipe. That is
a commercial decision as much as a technical one, and it needs to be made before
any code is written.

For a SaaS serving many companies, this also raises a question worth settling
early: does each customer bring their own GSP credentials, or do we hold a single
GSP contract and multiplex our customers through it? The second is the normal SaaS
model but has contractual and per-GSTIN implications to confirm with the GSP.

---

## 3. Credentials and sandbox

- NIC runs an **e-invoice API sandbox** at `einv-apisandbox.nic.in`. GSPs, ERPs,
  ECOs and notified taxpayers register there, verify by OTP, and receive a
  **Client ID and Client Secret**.
- Two credential pairs exist: Client ID / Client Secret belongs to the service
  provider, and User ID / Password is created by each taxpayer for their GSTIN to
  generate IRNs. A Client ID works across all GSTINs under the same PAN.
- Authentication returns a **token that is reused until it expires**, so the
  implementation needs a token cache, not a login per request.

Sandbox access is free and does not need the GSP contract signed, so a
feasibility spike can start immediately.

---

## 4. Compliance rules the product must respect

These are product requirements, not just integration details.

- **E-invoicing threshold: AATO above ₹5 crore**, in any financial year from
  2017-18 onwards. As of mid-2026 a reduction to ₹2 crore has been proposed but
  not notified. The system must therefore treat "does this company need
  e-invoicing" as a per-tenant setting, not a constant.
- **30-day reporting limit** for taxpayers with AATO of ₹10 crore and above: an
  invoice, credit note or debit note must be reported to the IRP within 30 days of
  its date. Past that, the IRP refuses the IRN and the invoice is not GST-valid.
  This one has a direct product consequence: **we need an ageing alert on
  unreported invoices**, not just a reporting button.
- **Exports are in scope.** An export invoice must be reported like any other. Our
  LUT handling stays; the LUT reference belongs on the invoice.
- SEZ units are exempt for their own outward supplies, but supplies *to* an SEZ
  still need an e-invoice.
- Exempt sectors: banking, insurance, NBFCs, goods transport agencies, passenger
  transport, multiplexes.

### The 2026 return changes matter for the roadmap

- **GSTR-3B is being hard-locked.** Outward liability in tables 3.1 and 3.2 has
  been locked since the July 2025 period, flowing from GSTR-1 / GSTR-1A / IFF.
  From July 2026, ITC in table 4A auto-populates from GSTR-2B and manual editing
  is discontinued.
- **IMS** (Invoice Management System) is where a taxpayer accepts, rejects or
  holds supplier invoices, and it drives GSTR-2B and therefore GSTR-3B.
  Discrepancies must be flagged before GSTR-2B generates on the 14th.

**Consequence:** automating "file GSTR-3B" is increasingly automating *the
reconciliation that precedes it*, not the submission itself. The submission is
becoming a confirmation step. The valuable product is accurate GSTR-1 data and an
IMS reconciliation workflow, not a file button.

---

## 5. What we already have, and what is missing

Already in place from the current data model:

- GSTIN on the company and on each client
- HSN/SAC per line item
- Place of supply as a validated state code
- Correct CGST/SGST/IGST split by supply type
- Export handling with LUT
- Compliant invoice numbering: 16 characters, restricted character set, per-FY series
- A document number series with no gaps

Missing for e-invoicing, and each is a real gap:

- **Recipient address detail.** The e-invoice schema needs the recipient's state
  code, PIN code, and place separately. We store a single free-text address.
- **Unit of measure and quantity codes.** The schema expects UQC codes.
- **Document type codes** (INV / CRN / DBN) and a credit/debit note model. We have
  no credit notes at all.
- **Supply type codes** (B2B, SEZWP, SEZWOP, EXPWP, EXPWOP, DEXP). Our
  `regular / interstate / export` needs mapping onto these, and export with and
  without payment of tax are different codes.
- **IRN storage**: IRN, acknowledgement number and date, the signed QR payload,
  and the cancellation window (24 hours).
- **AATO per tenant**, to decide applicability.

---

## 6. Proposed phasing

Each phase is independently useful and shippable.

### Phase 0: decide and spike (no customer-visible change)

1. Choose the commercial model: our GSP contract versus customer-supplied
   credentials. This gates everything.
2. Shortlist GSPs and compare on: sandbox quality, per-call pricing, GSTR-1 and
   IMS coverage, uptime terms, support.
3. Register on the NIC sandbox and prove the round trip: authenticate, generate
   an IRN for a dummy invoice, cancel it.

**Output:** a decision note and a working sandbox call. No product code.

### Phase 1: make our data e-invoice ready

Schema and UI work only, no external calls, so it carries no integration risk.

- Structured client addresses: line 1, line 2, city, state code, PIN.
- UQC on line items; document type; supply type mapping.
- Credit and debit notes as first-class documents.
- Per-company AATO and an "e-invoicing applicable" flag.
- A `document_irns` table ready for the response payload.

**Output:** every invoice can be serialised to the e-invoice JSON schema and
validated locally, before we can send anything.

### Phase 2: e-invoicing against the IRP

- A `GstIrpClient` behind an interface, with a fake implementation for tests.
- Token caching with expiry.
- Generate IRN on approval, which is a natural fit: our approval step already
  exists and already gates payment.
- Store IRN, acknowledgement number and date, and the signed QR; print the QR on
  the PDF, which is mandatory.
- Cancel within 24 hours; after that, a credit note is the only remedy.
- **A queue, retries, and an exception screen.** The IRP goes down; invoices must
  not be lost or silently unreported.
- The 30-day ageing alert on unreported invoices.

**Output:** legally valid e-invoices, and GSTR-1 becomes largely auto-populated
upstream.

### Phase 3: GSTR-1 assistance

- Build the GSTR-1 payload from our own data: B2B, B2CL, B2CS, exports, HSN
  summary, document series.
- Reconcile against what the portal reports as auto-populated from IRNs.
- Save the return as a draft through the GSP, and let a human file it.

**Deliberate limit: we prepare and submit a draft; we do not press file.** Filing
is an attested legal act. The first version should end with a review screen and
a human confirmation.

### Phase 4: GSTR-3B and IMS

- Pull GSTR-2B and the IMS list.
- A reconciliation workflow: accept, reject, hold, with reasons.
- Fetch the auto-populated GSTR-3B draft and show the differences against our
  books.

Given the hard-locking direction, most of the value here is reconciliation, and
the numbers are increasingly the portal's to compute.

---

## 7. Risks

- **GSP dependency.** Pricing, uptime and contract terms are outside our control,
  and switching means re-integrating. Keep the client behind an interface from
  day one.
- **Rules move.** Thresholds and return formats have changed repeatedly. Anything
  threshold-driven belongs in configuration, per tenant.
- **Filing liability.** Auto-filing a wrong return is the customer's legal
  exposure and our reputational one. Human confirmation on every filing action, at
  least until the reconciliation is demonstrably reliable.
- **Credential custody.** We would be holding GST credentials for customers.
  That needs encryption at rest, a considered key policy, an audit trail, and a
  clear position in the terms of service.
- **Sandbox is not production.** Behaviour differs; budget for a pilot with one
  real GSTIN.

---

## 8. Recommendation

Do Phase 0 and Phase 1 next, because they are entirely within our control and
Phase 1 is genuinely useful even if integration never happens: structured
addresses, credit notes, and UQC are gaps in the product today.

Do not start Phase 2 until the GSP is chosen and the sandbox round trip works.

Treat "file GST returns automatically" as the destination and e-invoicing as the
road: once invoices carry IRNs, GSTR-1 largely fills itself, which is most of the
value with a fraction of the liability.

---

## Sources

- [GST API Integration, Masters India](https://www.mastersindia.co/goods-and-services-tax-gst-api/)
- [GST Suvidha Provider (GSP) under GST, Saral](https://saral.pro/blogs/gst-suvidha-provider-gsp/)
- [New GST Return Filing System 2026, IncorpX](https://www.incorpx.io/blog/new-gst-return-filing-system-changes-2026)
- [E-Invoicing Rules in India 2026, Tally Solutions](https://tallysolutions.com/accounting/e-invoicing-rules-in-india/)
- [Invoice Registration Portal (IRP) guide, Binary Semantics](https://www.binarysemantics.com/blogs/invoice-registration-portal-irp-under-gst-complete-updated-guide/)
- [E-Invoice Limit FY 2026-27, Accountune](https://accountune.com/e-invoicing-compulsory-india-small-business-2026)
- [GST-NIC e-Invoice API Developer's Portal (sandbox)](https://einv-apisandbox.nic.in/)
- [API Credentials, NIC sandbox](https://einv-apisandbox.nic.in/apicredentials.html)
- [Setting up the API system for e-invoicing, ClearTax](https://cleartax.in/s/e-invoicing-api-system)
- [Different modes of API integration for e-invoicing, ClearTax](https://cleartax.in/s/e-invoicing-api-integration-modes)
- [How to select a GSP for e-invoicing API access, ClearTax](https://cleartax.in/s/gst-suvidha-provider-gsp-e-invoicing-api-access)
- [Revised time limit for e-invoice reporting, AATO ₹10 crore and above (einvoice6.gst.gov.in)](https://einvoice6.gst.gov.in/content/revised-time-limit-for-e-invoice-reporting-for-businesses-with-aato-of-%E2%82%B910-crores-above/)
- [GST e-invoice 30-day rule for ₹10 crore firms, IndiaFilings](https://www.indiafilings.com/learn/gst-einvoice-30-day-rule-10crore-turnover)
- [E-invoice reporting for export invoices within 30 days, GimBooks](https://www.gimbooks.com/blog/e-invoice-reporting-export-invoices-within-30-days/)
- [GSTR-3B hard locking and IMS guide, ClearTax Advisors](https://cleartaxadvisors.in/gstr-3b-hard-locking-ims-guide/)
- [GST updates July 2026: GSTR-3B auto-population and hard locking, TaxClear](https://taxclear.in/gst-updates-july-2026-gstr-3b-auto-population-hard-locking-3-year-return-bar-itc-matching-and-penalties/)
