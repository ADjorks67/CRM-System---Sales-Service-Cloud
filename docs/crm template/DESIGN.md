---
name: Obsidian & Butter Enterprise
colors:
  surface: '#121314'
  surface-dim: '#121314'
  surface-bright: '#39393a'
  surface-container-lowest: '#0d0e0f'
  surface-container-low: '#1b1c1d'
  surface-container: '#1f2021'
  surface-container-high: '#292a2b'
  surface-container-highest: '#343536'
  on-surface: '#e3e2e3'
  on-surface-variant: '#cfc6b1'
  inverse-surface: '#e3e2e3'
  inverse-on-surface: '#303031'
  outline: '#98907d'
  outline-variant: '#4c4636'
  surface-tint: '#e2c559'
  primary: '#ffe58d'
  on-primary: '#3b2f00'
  primary-container: '#e6c85c'
  on-primary-container: '#665300'
  inverse-primary: '#715d00'
  secondary: '#e5bfa9'
  on-secondary: '#432b1c'
  secondary-container: '#5c4131'
  on-secondary-container: '#d3ad99'
  tertiary: '#d8e7ff'
  on-tertiary: '#223145'
  tertiary-container: '#bbcbe4'
  on-tertiary-container: '#46566b'
  error: '#ffb4ab'
  on-error: '#690005'
  error-container: '#93000a'
  on-error-container: '#ffdad6'
  primary-fixed: '#ffe177'
  primary-fixed-dim: '#e2c559'
  on-primary-fixed: '#231b00'
  on-primary-fixed-variant: '#554500'
  secondary-fixed: '#ffdbc8'
  secondary-fixed-dim: '#e5bfa9'
  on-secondary-fixed: '#2b1609'
  on-secondary-fixed-variant: '#5c4131'
  tertiary-fixed: '#d4e4fe'
  tertiary-fixed-dim: '#b8c8e1'
  on-tertiary-fixed: '#0c1c2f'
  on-tertiary-fixed-variant: '#38485c'
  background: '#121314'
  on-background: '#e3e2e3'
  surface-variant: '#343536'
typography:
  headline-xl:
    fontFamily: Plus Jakarta Sans
    fontSize: 36px
    fontWeight: '600'
    lineHeight: 44px
    letterSpacing: -0.03em
  headline-xl-mobile:
    fontFamily: Plus Jakarta Sans
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 36px
    letterSpacing: -0.02em
  headline-lg:
    fontFamily: Plus Jakarta Sans
    fontSize: 26px
    fontWeight: '600'
    lineHeight: 34px
    letterSpacing: -0.02em
  headline-md:
    fontFamily: Plus Jakarta Sans
    fontSize: 20px
    fontWeight: '600'
    lineHeight: 28px
    letterSpacing: -0.01em
  headline-sm:
    fontFamily: Plus Jakarta Sans
    fontSize: 16px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.005em
  body-lg:
    fontFamily: Inter
    fontSize: 16px
    fontWeight: '400'
    lineHeight: 24px
    letterSpacing: 0em
  body-md:
    fontFamily: Inter
    fontSize: 14px
    fontWeight: '400'
    lineHeight: 20px
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 13px
    fontWeight: '400'
    lineHeight: 18px
    letterSpacing: 0em
  label-md:
    fontFamily: Inter
    fontSize: 12px
    fontWeight: '600'
    lineHeight: 16px
    letterSpacing: 0.04em
  label-sm:
    fontFamily: Inter
    fontSize: 11px
    fontWeight: '600'
    lineHeight: 14px
    letterSpacing: 0.05em
  metric-num-lg:
    fontFamily: Inter
    fontSize: 28px
    fontWeight: '600'
    lineHeight: 32px
    letterSpacing: -0.02em
  metric-num-md:
    fontFamily: Inter
    fontSize: 18px
    fontWeight: '600'
    lineHeight: 24px
    letterSpacing: -0.01em
rounded:
  sm: 0.25rem
  DEFAULT: 0.5rem
  md: 0.75rem
  lg: 1rem
  xl: 1.5rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-desktop: 1.5rem
  margin: 1rem
  margin-desktop: 2rem
  space-xs: 0.25rem
  space-sm: 0.5rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
---

## Brand & Style

This design system defines a dark luxury enterprise CRM (Sales & Service Cloud) tailored for high-stakes deal execution, account relationship management, and white-glove client servicing. Built natively around Laravel Blade component patterns, the visual language departs entirely from generic, sterile SaaS interfaces. Instead, it embodies the gravity of an executive terminal, high-end private banking software, and architectural editorial design.

### Design Movements
- **Dark Luxury Editorial:** Anchored by ultra-deep blacks, rich charcoal ash, and layered undertones of espresso brown and deep midnight navy.
- **Warm Minimalist Restraint:** Surfaces avoid icy, cold digital grays. Textures and tones leverage warm off-whites, roasted browns, and muted butter yellow as a bespoke, deliberate highlight.
- **Monolithic Enterprise Architecture:** Dense tabular data, multi-tiered detail sheets, structured metadata badges, and split-pane navigation organized with geometric discipline and balanced negative space.

### Emotional Target
The interface conveys institutional authority, discretion, surgical precision, and unhurried confidence. Interactions feel deliberate, quiet, and tactile—free of hyperactive neons, glass-blur gimmicks, or intrusive decorative noise.

## Colors

The palette is engineered around deep architectural neutrals with controlled tonal warmth, punctuated by a signature butter yellow accent. Emerald is strictly quarantined to positive revenue, ARR growth, and terminal success states.

### Core Architecture Tokens (Dark Mode - Default)
- **Canvas Base (`#0A0A0A`):** The primary view background layer.
- **Content Surface (`#101112`):** Primary workspace canvas, pipeline columns, and content viewports.
- **Ash Surface (`#17191A`):** Sub-panels, secondary sidebars, and nested groupings.
- **Secondary Ash Panel (`#1D1F20`):** Inset controls, search trays, and elevated tab lists.
- **Card Background (`#191A1B`):** Standard resting state for account cards, deal cards, and table rows.
- **Elevated Card (`#222222`):** Contextual popovers, active drawer contents, and modal dialogs.
- **Structural Outlines:** Borders rest at `#303131`; internal dividers and micro-hairlines sit at `#282929`.

### Deep Tonal Accents
- **Espresso / Warm Brown Series:** 
  - Deep Espresso: `#211915` (tinted container fills)
  - Dark Brown: `#2D211B` (hover states for brown surfaces)
  - Warm Brown: `#4A3529` (inactive border frames)
  - Soft Brown: `#705342` (secondary button borders, tertiary badges)
- **Navy Deep Series:**
  - Deep Navy: `#0D1724`
  - Dark Blue: `#122235` (used for executive/system action controls)
  - Slate Navy: `#1B3045`
  - Muted Blue: `#40566B`
- **Butter Yellow Series (Primary Warm Accent):**
  - Deep Butter: `#B99A3E` (pressed states, warning borders)
  - Butter Yellow: `#E6C85C` (primary CTA, active navigation indicators, key targets)
  - Soft Butter: `#F1D979` (active icons, badge highlights)
  - Pale Butter: `#F7E9A8` (text contrast on dark accent pills)

### Functional Feedback Tokens
- **Success / Positive Growth (Strict Usage):** Primary Emerald `#10B981`, Mint `#34D399`, Deep Emerald `#087F5B`. Used only for closed-won deals, quota achievement, positive delta metrics, and healthy SLAs.
- **Alert / Destructive:** Danger `#D94A4A`, Soft Red `#F07878`, Warning Amber `#D9A441`.

### High-Fidelity Text Hierarchy
- **Text Primary (`#F5F3EE`):** Warm off-white, high-contrast clarity for headers, metric values, and primary labels.
- **Text Secondary (`#D2CFCA`):** De-emphasized metadata, table cells, and regular labels.
- **Text Muted (`#92908C`):** Timestamps, form helper text, and inactive toggles.
- **Text Disabled (`#5F5E5B`):** Ghost elements, placeholder text, and locked records.

### Dual-State Inversion: Light Theme Guidelines
When rendering in light mode, the primary app sidebar stays fixed at dark `#0B0C0C` to preserve enterprise identity and visual grounding. Main content switches to ivory/linen surfaces: Page Background `#F7F3E8`, Main Content `#FBF9F2`, Cards `#FFFDF7`, Secondary Cards `#F1ECE0`, Ash Panel `#E5E2DA`, and Espresso Light `#E5D7CC`. Butter Yellow `#E6C85C` remains the consistent interaction thread.

## Typography

The typographic hierarchy combines structural authority with financial legibility. 

### Font Selection & Pairing
- **Headlines & Section Titles:** Set in **Plus Jakarta Sans** with calibrated negative tracking (`-0.01em` to `-0.03em`) and medium/semibold weights (`500`/`600`). This gives client profiles, deal overviews, and board dashboards an architectural, premium poise.
- **Body & Controls:** Set in **Inter** for optimal micro-legibility at 13px and 14px in high-density CRM scenarios (timeline threads, email logs, conversation trees).
- **Financial Metrics & KPIs:** Rendered using Inter with OpenType feature flags `font-feature-settings: "tnum" 1, "cv05" 1, "zero" 1` enabled across all currency fields, pipeline totals, ARR figures, and win-probability percentages. This ensures clean columnar alignment and zero visual jitter across real-time data feeds.

## Layout & Spacing

The layout model is optimized for dense enterprise workflows, leveraging an asymmetric multi-pane master-detail pattern.

### Structure & Panes
- **Primary App Bar (Global Navigation):** Fixed 64px width on the left (`#0B0C0C`), housing workspace switcher, global search trigger, module icons, and profile status.
- **Contextual Secondary Sidebar:** 240px to 280px collapsible panel (`#17191A`), containing pipeline filters, saved views, territory trees, and mailbox queues.
- **Main Working Canvas (`#101112`):** Fluid-width container operating on a 12-column grid system with `gutter-desktop` (24px) and internal section padding of `margin-desktop` (32px).
- **Contextual Inspector / Service Flyout:** Fixed 380px slide-over or dockable right panel (`#17191A`) for deal history, audit trails, and quick activity logging.

### Breakpoints & Fluid Adaptation
- **Desktop (1440px+):** Full 3-pane orchestration visible (Primary Nav + Secondary Filter + Canvas + Inspector).
- **Laptop / Compact Desktop (1024px - 1439px):** Inspector defaults to an off-canvas drawer layered above the content.
- **Tablet / Mobile (<1024px):** Sidebars collapse into a drawer overlay; pipeline views automatically toggle from multi-column drag-and-drop boards into a stacked list view with segmented stage pickers.

## Elevation & Depth

This system intentionally avoids gratuitous drop shadows or blurred atmospheric glow effects. Depth is expressed through structural tonal stepping and razor-sharp, low-contrast border boundaries.

### Tonal Hierarchy
- **Level 0 (Recessed Base):** `#0A0A0A` — The application foundation and global navigation shell.
- **Level 1 (Canvas Floor):** `#101112` — Primary work surface.
- **Level 2 (Panels & Sub-surfaces):** `#17191A` — Secondary sidebars, header strips, and container wells.
- **Level 3 (Default Cards & Data Rows):** `#191A1B` bound with a 1px solid border of `#303131`.
- **Level 4 (Interactive Hover & Elevated Cards):** `#222222` with a subtle highlight top-border (`border-t: 1px solid #4A3529` or `#353738`).
- **Level 5 (Modals, Overlays, and Command Palettes):** `#1D1F20` resting on an absolute backdrop barrier of `rgba(10, 10, 10, 0.75)` with an ultra-deep structural boundary shadow: `0 20px 48px rgba(0, 0, 0, 0.6)`.

### Border Discipline
Dividers use `#282929` for subtle horizontal splits between list items. Form elements, pill tags, and cards use `#303131` to delineate boundaries against the content canvas.

## Shapes

The design uses a balanced `roundedness: 2` (0.5rem / 8px default corner radius). This avoids both brutalist sharpness and consumer-style circular pill shapes, reinforcing enterprise craftsmanship and structural clarity.

### Application Rules
- **Standard UI Elements (Buttons, Inputs, Selects, Dropdowns):** `rounded-md` (8px).
- **Cards, Modals, Pipeline Buckets, Split Sheets:** `rounded-lg` (12px to 16px).
- **Status Pills, Count Badges, Table Tags:** `rounded` (4px to 6px) to maintain rigid geometric alignment next to dense tabular data.
- **User Avatars:** Circles (50% / `rounded-full`) to contrast against rectangular data cards.

## Components

Components follow reusable Laravel Blade conventions (`<x-crm.button>`, `<x-crm.card>`, `<x-crm.input>`), providing predictable variants and high visual discipline.

### Buttons (`<x-crm.button>`)
Height sits uniformly at 40px (default) or 32px (compact data table action). Font size is 13px/14px semibold with 8px radius.
- **Primary:** Background `#E6C85C` with rich charcoal `#171717` text. On hover, background shifts to `#F1D979`. In active/pressed state, deepens to `#B99A3E`.
- **Secondary (Tactile Espresso):** Transparent background, 1px solid `#705342` border, text `#E5D7CC`. On hover, background transitions to `rgba(112, 83, 66, 0.15)` with text `#F5F3EE`.
- **Deep Navy (Executive Action):** Background `#122235`, border 1px solid `#1B3045`, text `#F5F3EE`. On hover, background transitions to `#1B3045`.
- **Success (Deal Won):** Background `#10B981`, text `#06110C`, font semibold.
- **Danger (Destructive):** Background `#D94A4A`, text `#FFFFFF`. On hover: `#F07878`.

### Form Fields & Inputs (`<x-crm.input>`)
- **Default State:** Background `#17191A`, border 1px solid `#303131`, text `#F5F3EE`, placeholder `#5F5E5B`. Height: 40px, padding: 0 12px.
- **Focus State:** Border shifts to `#E6C85C` with a clean, low-intensity outer ring `box-shadow: 0 0 0 1px #E6C85C`.
- **Error State:** Border shifts to `#D94A4A`, subtext renders in `#F07878`.

### Checkboxes & Radios
- Box size: 16px x 16px, corner radius 4px (checkbox) or circle (radio).
- Unchecked: Background `#17191A`, border `#353738`.
- Checked: Background `#E6C85C`, border `#E6C85C`, mark/icon `#171717`.

### Two-Tone Navigation Sidebar (`<x-crm.sidebar>`)
- Main column `#0B0C0C`, secondary panel `#17191A`.
- **Navigation Item:** Base background transparent, icon and text `#858580`.
- **Active Navigation Item:** Background `#292827`, left border bar (3px width) `#E6C85C`, icon `#F1D979`, text `#F1EFEB`.

### Pipeline Deal Cards & Data Cards (`<x-crm.card>`)
- Surface `#191A1B`, border 1px solid `#303131`, padding 16px.
- Header row includes company badge and deal value in bold tabular figures.
- Hover interaction: Surface shifts to `#222222`, border shifts to `#4A3529`.

### Chips & Metadata Badges (`<x-crm.badge>`)
- **Butter Accent Badge:** Background `rgba(230, 200, 92, 0.12)`, border 1px solid `rgba(230, 200, 92, 0.25)`, text `#F1D979`.
- **Deal Stage Won (Emerald):** Background `rgba(16, 185, 129, 0.12)`, border 1px solid `rgba(16, 185, 129, 0.3)`, text `#34D399`.
- **Deal Stage Lost (Muted/Neutral):** Background `#242627`, border 1px solid `#353738`, text `#92908C`.
- **Navy Corporate Tier:** Background `#0D1724`, border 1px solid `#1B3045`, text `#F5F3EE`.