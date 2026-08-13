---
name: The Higglers Invoicing
description: A ledger desk for GST compliant quotations and invoices, where the document leads and the interface recedes.
colors:
  ink-indigo: "#4f46e5"
  ink-indigo-deep: "#4338ca"
  ink-indigo-glow: "#6366f1"
  ink-indigo-edge: "#818cf8"
  ink-indigo-wash: "#eef2ff"
  graphite: "#1f2937"
  graphite-deep: "#111827"
  pencil-gray: "#6b7280"
  rule-gray: "#d1d5db"
  hairline-gray: "#e5e7eb"
  ledger-paper: "#f3f4f6"
  paper-tint: "#f9fafb"
  sheet-white: "#ffffff"
  settled-green: "#16a34a"
  settled-green-ink: "#166534"
  settled-green-wash: "#dcfce7"
  overdue-red: "#dc2626"
  overdue-red-ink: "#991b1b"
  overdue-red-wash: "#fee2e2"
  in-flight-blue: "#1e40af"
  in-flight-blue-wash: "#dbeafe"
  internal-amber: "#92400e"
  internal-amber-wash: "#fef3c7"
typography:
  display:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.875rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "normal"
  headline:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.4
    letterSpacing: "normal"
  title:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "1.125rem"
    fontWeight: 700
    lineHeight: 1.4
    letterSpacing: "normal"
  body:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.875rem"
    fontWeight: 400
    lineHeight: 1.5
    letterSpacing: "normal"
  label:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 700
    lineHeight: 1
    letterSpacing: "0.05em"
  action:
    fontFamily: "Figtree, ui-sans-serif, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 600
    lineHeight: 1
    letterSpacing: "0.1em"
  document-title:
    fontFamily: "DejaVu Sans, sans-serif"
    fontSize: "17pt"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "0.08em"
  document-body:
    fontFamily: "DejaVu Sans, sans-serif"
    fontSize: "9.5pt"
    fontWeight: 400
    lineHeight: 1.45
    letterSpacing: "normal"
rounded:
  control: "6px"
  panel: "8px"
  inset: "12px"
  pill: "9999px"
  document: "0"
spacing:
  xs: "8px"
  sm: "12px"
  md: "16px"
  lg: "24px"
  xl: "32px"
  2xl: "48px"
components:
  button-primary:
    backgroundColor: "{colors.graphite}"
    textColor: "{colors.sheet-white}"
    typography: "{typography.action}"
    rounded: "{rounded.control}"
    padding: "8px 16px"
  button-primary-hover:
    backgroundColor: "#374151"
    textColor: "{colors.sheet-white}"
  button-action:
    backgroundColor: "{colors.ink-indigo}"
    textColor: "{colors.sheet-white}"
    typography: "{typography.action}"
    rounded: "{rounded.control}"
    padding: "8px 16px"
  button-action-hover:
    backgroundColor: "{colors.ink-indigo-deep}"
    textColor: "{colors.sheet-white}"
  button-secondary:
    backgroundColor: "{colors.sheet-white}"
    textColor: "#374151"
    typography: "{typography.action}"
    rounded: "{rounded.control}"
    padding: "8px 16px"
  button-danger:
    backgroundColor: "{colors.overdue-red}"
    textColor: "{colors.sheet-white}"
    typography: "{typography.action}"
    rounded: "{rounded.control}"
    padding: "8px 16px"
  input-text:
    backgroundColor: "{colors.sheet-white}"
    textColor: "{colors.graphite-deep}"
    typography: "{typography.body}"
    rounded: "{rounded.control}"
    padding: "8px 12px"
  rail-item:
    backgroundColor: "transparent"
    textColor: "#4b5563"
    typography: "{typography.body}"
    rounded: "{rounded.panel}"
    padding: "12px 16px"
  rail-item-active:
    backgroundColor: "{colors.ink-indigo}"
    textColor: "{colors.sheet-white}"
    typography: "{typography.body}"
    rounded: "{rounded.panel}"
    padding: "12px 16px"
  panel:
    backgroundColor: "{colors.sheet-white}"
    textColor: "{colors.graphite-deep}"
    rounded: "{rounded.panel}"
    padding: "24px"
  panel-header:
    backgroundColor: "{colors.paper-tint}"
    textColor: "{colors.graphite-deep}"
    typography: "{typography.title}"
    padding: "20px 24px"
  status-pill:
    backgroundColor: "{colors.ledger-paper}"
    textColor: "{colors.graphite}"
    typography: "{typography.label}"
    rounded: "{rounded.pill}"
    padding: "2px 8px"
  empty-state:
    backgroundColor: "{colors.paper-tint}"
    textColor: "{colors.pencil-gray}"
    typography: "{typography.body}"
    rounded: "{rounded.panel}"
    padding: "40px 24px"
---

# Design System: The Higglers Invoicing

## Overview

**Creative North Star: "The Ledger Desk"**

Paper discipline brought to screen. This is a workspace for issuing money documents that a chartered accountant will read months later, so the system behaves like a well-kept ledger: ruled rows, squared panels, quiet grays, and a single indigo pen reserved for the one thing you can act on. The document is the hero. The interface is the desk it sits on, and a desk that draws attention to itself is a badly made desk.

The register is crisp, confident, and professional rather than merely calm. There is real contrast in the type scale and real conviction in the state colours; nothing is timid. But nothing is expressive either. Confidence here comes from exactness: aligned numerals, consistent intervals, labels that always sit in the same place, and colour that always means something. A screen that looks slightly boring and reads perfectly at a glance has succeeded.

Depth is drawn, not lifted. Hairline borders and tonal fills carry the structure; shadows are a response to state, not a decoration applied to every surface. And the system deliberately keeps two worlds: the screen speaks in Ink Indigo, the printed document speaks in Graphite. A tax invoice should read as a formal record, never as a screenshot of somebody's app.

**Key Characteristics:**

- Grays carry structure; Ink Indigo appears once per region, where you act.
- Every value that needs a name gets a small uppercase label above it.
- Tables are ruled, not boxed: hairline dividers, tinted header row, no cell borders.
- Colour is state. Green settled, red overdue, blue in flight, amber internal.
- Two accents, on purpose: Ink Indigo on screen, Graphite on paper.
- Numbers are right-aligned, tabular, and always carry their currency.

## Colors

A near-monochrome ledger palette: five neutrals doing nearly all the work, one indigo for action, and four state colours that never appear except as status.

### Primary

- **Ink Indigo** (`{colors.ink-indigo}`): The pen. Primary actions (Add Note, Save Payment Record), the active rail item's fill, links, and focus rings. It marks the one thing in a region that advances the task. **Ink Indigo Deep** (`{colors.ink-indigo-deep}`) is its hover, **Ink Indigo Glow** (`{colors.ink-indigo-glow}`) the focus ring, **Ink Indigo Edge** (`{colors.ink-indigo-edge}`) the 2px underline on the active top-nav item, and **Ink Indigo Wash** (`{colors.ink-indigo-wash}`) the tint behind an emphasised form block or a related-record chip.

### Secondary

- **Graphite** (`{colors.graphite}`): The document's voice and the neutral command colour. It is the accent of the printed invoice and quotation (the rule under the header, the document title, the grand-total band) and the fill of the neutral primary button (Download PDF). **Graphite Deep** (`{colors.graphite-deep}`) is body headings on screen.

### Neutral

- **Pencil Gray** (`{colors.pencil-gray}`): Every micro-label, table header, and secondary meta line. If text names a value rather than being the value, it is Pencil Gray.
- **Rule Gray** (`{colors.rule-gray}`): Input borders and the borders of anything a pointer can act on.
- **Hairline Gray** (`{colors.hairline-gray}`): Dividers, panel edges, table rules. The most-used structural colour in the system.
- **Ledger Paper** (`{colors.ledger-paper}`): The application canvas behind every page, and the tint of a document table's header row.
- **Paper Tint** (`{colors.paper-tint}`): The half-step above canvas used for panel header strips, empty states, and totals bands.
- **Sheet White** (`{colors.sheet-white}`): Every raised surface, and the printed sheet itself.

### Tertiary

State colours. Each exists as an ink and a wash, used together as a pill.

- **Settled Green** (`{colors.settled-green}` ink `{colors.settled-green-ink}`, wash `{colors.settled-green-wash}`): Paid invoices, approved quotations, received amounts, and the Clone to Invoice action, which is the moment a quotation becomes real money.
- **Overdue Red** (`{colors.overdue-red}` ink `{colors.overdue-red-ink}`, wash `{colors.overdue-red-wash}`): Overdue invoices, rejected quotations, destructive actions.
- **In-flight Blue** (`{colors.in-flight-blue}`, wash `{colors.in-flight-blue-wash}`): Sent. Something is with the client and the ball is not in your court.
- **Internal Amber** (`{colors.internal-amber}`, wash `{colors.internal-amber-wash}`): Team-only territory. Private note banners, the Internal Only badge, and the Invoice Pending flag on an approved quotation. Amber never appears on anything a client can see, which is exactly what makes it legible as an internal signal.

### Named Rules

**The One Pen Rule.** Ink Indigo marks at most one action per region. If two buttons in a panel are indigo, one of them is wrong. Secondary actions are white with a Rule Gray border; neutral-but-primary actions (Download PDF) are Graphite.

**The Two Worlds Rule.** Ink Indigo never appears on the printed document, and Graphite never becomes the screen's action colour. The document accent is a single value in `config/document-templates.php`; changing it changes the paper world only.

**The Colour Means State Rule.** Green, red, blue, and amber are reserved for status. They are never used to decorate, to differentiate sections, or to make a panel feel friendlier. A green heading with no settled meaning is a defect.

## Typography

**Display / Body Font:** Figtree (with `ui-sans-serif, system-ui, sans-serif`), served from fonts.bunny.net at weights 400, 500, and 600. One family does everything on screen.

**Document Font:** DejaVu Sans. Not a style choice: Dompdf embeds it reliably and it carries the rupee sign and the full Latin range the PDF needs.

**Character:** Figtree is a humanist sans with slightly open apertures and a tall x-height, which keeps 12px uppercase labels legible and keeps dense numeric tables from turning into gray mush. The pairing is deliberately unremarkable on screen and deliberately institutional on paper.

### Hierarchy

- **Display** (700, 1.875rem/30px): Dashboard figures only. The outstanding, earned, and overdue totals. Nothing else earns this size.
- **Headline** (600, 1.25rem/20px): The page heading in the layout's header slot. One per page.
- **Title** (700, 1.125rem/18px): Panel headers inside the content column.
- **Body** (400, 0.875rem/14px, 1.5): Everything else. Table cells, field values, note text, prose.
- **Label** (700, 0.75rem/12px, uppercase, 0.05em): Table column headers and micro-labels above values. Pencil Gray.
- **Action** (600, 0.75rem/12px, uppercase, 0.1em): Button and control labels. The widest tracking in the system, and the only place it appears.
- **Document Title** (700, 17pt, 0.08em, uppercase) and **Document Body** (400, 9.5pt, 1.45): The paper scale, which runs in points because it is laid out against an A4 page, not a viewport.

### Named Rules

**The Label Above Rule.** A named value gets a 12px uppercase Pencil Gray label directly above it, not an inline `Label:` prefix. The label is the quietest thing in the pair; the value is the loudest.

**The One Display Rule.** Only aggregate money figures get Display size. Scaling up a heading to signal importance is not available; importance is signalled by position and by colour.

## Layout

The application canvas is Ledger Paper (`{colors.ledger-paper}`) at full height, with a white top navigation bar (64px, hairline bottom border) and an optional white page header strip carrying a shadow. Content sits in a `max-w-7xl` centred container with `py-12` vertical padding and responsive side padding (16px, then 24px at `sm`, then 32px at `lg`).

Spacing follows Tailwind's 4px base. The steps in real use are 8, 12, 16, 24, 32, and 48px: 8 and 12 for intra-component rhythm, 16 for field gaps, 24 for panel padding, 32 for the gap between the rail and the content column, 48 for page-level breathing.

**Detail workspaces use a two-column rail.** Invoice and quotation detail pages place a fixed 16rem (256px) left rail of section buttons against a fluid content column (`flex-1 min-w-0`), separated by a 32px gap, with the rail sticky from `md` upward. Below `md` the two stack, rail first, and the rail loses its sticky behaviour. Only one content panel is visible at a time, switched by Alpine state rather than navigation, so the workspace never scrolls past sections the reader did not ask for.

**Index pages are single-column tables** inside one white panel, horizontally scrollable at narrow widths (`overflow-x-auto`) rather than reflowed into cards. Forms are single-column with a two-column field grid at `md` and a full-width item table beneath.

**The printed document is a fixed A4 sheet**: 210mm by 297mm, 14mm top and bottom by 12mm side margins, rendered as tables and blocks only. Dompdf supports neither flexbox nor grid, so no screen layout technique transfers to it. On screen the same template renders as a centred sheet on a Ledger Paper background with a hairline border and a single soft shadow.

### Named Rules

**The Fixed Rail Rule.** The section rail is 16rem, always. It does not scale with the viewport, because a nav item that changes width between pages stops being recognisable. Both detail pages hold this; a fluid rail is drift, not a second valid pattern.

**The One Sheet Rule.** The document view is a single surface. The panel is the sheet, so nothing inside it carries a second background, border, shadow, or padding step. A card inside a card is always wrong here.

**The One Panel Rule.** A detail workspace shows exactly one content panel at a time. Do not stack sections vertically and expect the rail to act as an anchor list.

## Elevation & Depth

Structure is drawn, not lifted. Hairline Gray borders and half-step tonal fills do nearly all the work of separating surfaces, and most of the interface is flat at rest. Shadow is reserved for two jobs: marking a surface that genuinely floats above the page, and confirming a state change.

### Shadow Vocabulary

- **Page header** (`box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1)`): The one persistent shadow in the chrome, separating the page heading strip from scrolling content.
- **Panel rest** (`box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05)`): The barely-there lift on content panels. Close enough to flat that the border still reads as the edge.
- **State lift** (`box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1)`): The active rail item and hovered inactive rail items. This is a response, not a resting property.
- **Floating layer** (`box-shadow: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1)`): Dropdowns and modals only, paired with a `ring-1 ring-black/5` edge.
- **Focus ring** (`box-shadow: 0 0 0 2px #fff, 0 0 0 4px {colors.ink-indigo-glow}`): Keyboard focus on every interactive control. Never removed, never replaced with an outline-none and nothing else.

### Named Rules

**The Hairline First Rule.** Reach for a border or a tonal fill before reaching for a shadow. If a surface needs a shadow to be distinguishable, its border or its spacing is wrong.

**The Shadow Is A Verb Rule.** Shadows describe what something is doing (floating, active, hovered), never what something is. A panel does not get a heavier shadow because it is more important.

## Shapes

Rectilinear with softened corners, tightening as elements get smaller and more functional. Controls and inputs use a 6px radius; panels and rail items use 8px; inset emphasis blocks inside a panel (the payment form, a bordered table wrapper) use 12px; status pills are fully rounded. Nothing in the system is a circle except an avatar initial and a timeline node.

Borders are 1px Hairline Gray almost everywhere, stepping to Rule Gray on anything interactive. Three deliberate exceptions carry meaning: a 2px left border in Ink Indigo Wash marks a note in a thread, a 4px left border marks the active item in the mobile nav, and a dashed 1px Hairline Gray border marks an empty state, which is the only dashed edge in the system.

**The printed document has no radii at all.** Square corners, 1px rules, a 2px Graphite rule under the header, and a 1.5px Graphite band around the grand total. Paper does not have rounded corners.

### Named Rules

**The Dashed Means Empty Rule.** A dashed border means "nothing here yet" and nothing else. Never use it for emphasis, for drop targets, or for a placeholder that actually contains data.

## Components

### Buttons

- **Shape:** Softly squared (6px), inline-flex, 8px by 16px padding, Action typography (12px, 600, uppercase, 0.1em tracking).
- **Primary (neutral):** Graphite fill, Sheet White text. The default page-level action, e.g. Download PDF. Hover lifts to `#374151`, active settles to Graphite Deep.
- **Action (indigo):** Ink Indigo fill, Sheet White text, for the action that advances the task inside a panel. Hover Ink Indigo Deep.
- **Affirmative:** Settled Green fill for the moment of commitment (Clone to Invoice). Used once per screen at most.
- **Secondary:** Sheet White fill, Rule Gray border, `#374151` text, `shadow-sm`. Hover tints to Paper Tint.
- **Danger:** Overdue Red fill. Always paired with a confirm dialog.
- **Quiet destructive:** For row-level deletes inside a list, a bare 12px uppercase Pencil Gray text button that turns Overdue Red on hover. No fill, no border.
- **Focus:** 2px Ink Indigo Glow ring with a 2px white offset on every variant.

### Inputs / Fields

- **Style:** Sheet White, 1px Rule Gray border, 6px radius, `shadow-sm`, Body typography. Selects, text inputs, and textareas share one silhouette; `@tailwindcss/forms` supplies the reset.
- **Label:** A 14px medium `#374151` label above the field, at 4px remove.
- **Focus:** Border shifts to Ink Indigo Glow and a matching 1px ring appears. The border colour change and the ring arrive together.
- **Error:** A 14px Overdue Red message list below the field. The field border is not recoloured; the message carries the state.
- **Hint:** 12px Pencil Gray below the field, used sparingly, e.g. "Add your LUT numbers as company custom fields to pick them from a list here."

### Cards / Containers

- **Corner style:** 8px.
- **Background:** Sheet White on a Ledger Paper canvas.
- **Header strip:** Optional. Paper Tint fill, 20px by 24px padding, hairline bottom border, Title typography. Status badges sit right-aligned in this strip.
- **Body padding:** 24px.
- **Shadow:** Panel rest only. See Elevation.
- **Border:** None on the outer panel; hairlines are used inside it to separate rows and blocks.

### Tables

- **Header:** Paper Tint or Ledger Paper row, 12px uppercase Pencil Gray labels with 0.05em tracking, 12px by 24px padding.
- **Body:** 16px by 24px cells, Body typography, `divide-y` hairline rules between rows. No vertical rules and no cell borders.
- **Numerics:** Right-aligned, semibold, always prefixed with the document's currency.
- **Hover:** Rows in an actionable list tint to Paper Tint. Static tables do not react.
- **Narrow widths:** The table scrolls inside its panel. It does not become cards.

### Status Pills

- **Style:** Fully rounded, 2px by 8px, 12px semibold, wash background with ink text. One per state.
- **Vocabulary:** Draft is Ledger Paper on Graphite; Sent is In-flight Blue; Approved and Paid are Settled Green; Rejected and Overdue are Overdue Red; Invoice Pending and Internal Only are Internal Amber.

### Navigation

- **Top bar:** White, 64px, hairline bottom border, company logo at the left (falling back to the application mark when no logo is uploaded). Links are 14px medium with a 2px bottom border, transparent when inactive and Ink Indigo Edge when active, with the label darkening to Graphite Deep. On mobile the same links become a stacked list with a 4px left border and an Ink Indigo Wash fill when active.
- **Section rail:** The signature navigation of this system. See below.

### Section Rail (signature component)

The left rail on invoice and quotation detail pages. Each item is a full-width left-aligned button, 12px by 16px, 8px radius, 14px bold label with the section icon right-aligned at 16px.

- **Inactive:** Transparent fill, `#4b5563` label, transparent border. On hover it gains a Sheet White fill, a Hairline Gray border, and a small shadow, so the surface appears to rise under the pointer.
- **Active:** Ink Indigo fill, Sheet White label, state-lift shadow. This is the only persistently coloured surface in the chrome, which is what makes the current section unmistakable.
- **Sub-label:** An item may carry a second line at 12px, normal case, in `#9ca3af` (or Ink Indigo Wash tint when active). Private Notes uses it to say "Not visible to the client" without opening the section.

### Note Thread (signature component)

Internal notes on invoices and quotations. Each entry is a block with a 2px Ink Indigo Wash left border and 16px left padding: the note body in Body type, then a 12px Pencil Gray byline reading author, a middot, and the timestamp in `d M Y, H:i`. A quiet destructive Delete sits at the top right of each entry. The composer sits above the thread, newest entry first.

The panel that holds a note thread opens with an Internal Amber banner stating that the notes are never shown to the client. That banner is not decoration; it is the contract.

## Do's and Don'ts

### Do:

- **Do** put a 12px uppercase Pencil Gray label above every named value, and let the value carry the weight.
- **Do** spend Ink Indigo on exactly one action per region, and use Graphite for a page-level action that is primary but not the task's next step.
- **Do** separate surfaces with a 1px Hairline Gray border or a Paper Tint fill before considering a shadow.
- **Do** keep the section rail at a fixed 16rem and show exactly one content panel at a time.
- **Do** right-align money, keep it semibold, and print the currency code alongside it every time.
- **Do** give every empty region a real empty state: Paper Tint fill, dashed hairline border, one italic Pencil Gray sentence that says what would be here.
- **Do** mark anything internal in Internal Amber, and say plainly in words that the client cannot see it.
- **Do** keep the printed document in Graphite, square-cornered, and laid out with tables and blocks, because Dompdf supports nothing else.

### Don't:

- **Don't** put Ink Indigo on the printed document or Graphite on a screen action. The two worlds stay separate.
- **Don't** use green, red, blue, or amber for anything that is not a state. No coloured section headings, no decorative tints.
- **Don't** add a shadow to make something feel important. Shadows describe floating, hover, and active, nothing else.
- **Don't** scale a heading up past its role to signal priority. Display size belongs to aggregate figures only.
- **Don't** introduce a second font family on screen. Figtree does all of it, and DejaVu Sans exists only because the PDF renderer requires it.
- **Don't** use a dashed border for anything except an empty state.
- **Don't** reflow a data table into stacked cards at narrow widths. Let it scroll inside its panel.
- **Don't** put team context, workflow state, or private notes anywhere a client-facing document can reach.
