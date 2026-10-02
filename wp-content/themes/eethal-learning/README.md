# Eethal Learning — WordPress Theme

A classic (PHP) WordPress theme converted from the original static `VTNF.html` /
`VTNF.css` landing page. The design is unchanged; the content is now editable
from the WordPress admin.

---

## Install

1. Copy the whole `eethal-learning` folder into `wp-content/themes/`
   (or upload `eethal-learning.zip` via **Appearance → Themes → Add New → Upload Theme**).
2. Activate it under **Appearance → Themes**.
That is enough — `front-page.php` renders the landing page on the site root
either way, so no **Settings → Reading** change is required.

If you also want a blog, set **Settings → Reading → Your homepage displays** to
*A static page*, choose any published page as *Homepage* (its content is
ignored — the landing sections are used), and set a second page as *Posts
page*. That posts page is rendered by `index.php`.

On activation the site looks exactly like the original page: every section falls
back to the packaged default text and images until you replace them.

---

## Where each part of the page is edited

Every landing-page section has its own entry under **Appearance → Customize →
Eethal Front Page**, starting with a **Show this section** checkbox.

| Page section | Edited in |
|---|---|
| Logo | **Customize → Site Identity → Logo** |
| Menu | **Appearance → Menus**, *Primary Menu* location |
| Phone, WhatsApp, email, enrolment link | **Eethal Front Page → Contact & Links** |
| Hero text, button, image | **Eethal Front Page → Hero Section** |
| Benefits bar items | **Eethal Front Page → Benefits Bar** |
| Marquee items | **Eethal Front Page → Scrolling Marquee** |
| About copy, check list, stats (number, suffix, label), image | **Eethal Front Page → About Section** |
| Courses heading, default button text | **Eethal Front Page → Courses Section** |
| Course cards | **Courses** in the admin menu |
| Advantages heading and items | **Eethal Front Page → Why Choose Us** |
| Community photo | **Eethal Front Page → Community Photo** |
| Outcomes copy, check list, stat labels, image | **Eethal Front Page → Student Outcomes** |
| Mentors heading / Mentor cards | **Eethal Front Page → Mentors Section** / **Mentors** in the admin menu |
| Alumni heading / quotes | **Eethal Front Page → Alumni Stories** / **Testimonials** in the admin menu |
| FAQ heading / questions | **Eethal Front Page → FAQ Section** / **FAQs** in the admin menu |
| Enroll Now form (`/enroll/`): batch, heading, questions, choices, thank-you text | **Enroll Now** in the wp-admin menu, or **Eethal Front Page → Enroll Now Form** |
| Spam protection for both public forms: optional Cloudflare Turnstile keys, the messages people see | **Enroll Now** in the wp-admin menu, or **Eethal Front Page → Form Spam Protection** |
| Closing CTA (text, both buttons and links, labels) + footer credit | **Eethal Front Page → Call to Action & Footer** |
| Favicon | **Customize → Site Identity → Site Icon** |

List fields take one item per line. Icon lists (benefits, advantages) use
`icon | text`, e.g. `🎓 | 10+ Years Exp Trainers`.

On first activation `inc/seed.php` imports the packaged content once: the
course, mentor, testimonial and FAQ entries, the section images and logo (into
the Media Library, wired to their Customizer fields) and a Primary menu. It
never overwrites existing content.

The hero button, the CTA button, and any course card that has no link of its own
open the built-in **Enroll Now** form (`/enroll/`). To send them somewhere else,
set **Contact & Links → Enrolment form link**; leave it empty to use `/enroll/`.
The form's wording (heading, questions, choices, thank-you text, batch) is edited
on the **Enroll Now** page in the wp-admin menu (`inc/enroll-admin.php`). The same
fields also appear under **Eethal Front Page → Enroll Now Form** in the Customizer;
both are built from `eethal_enroll_setting_groups()`, so add new fields there.

### Content types

Each of the four content types is ordered by the **Order** field under *Page
Attributes* (lower numbers first), then by date.

- **Courses** — title, featured image, plus *Card subtitle*, *Button link* and
  *Button text*. Falls back to the excerpt when no subtitle is set.
- **Mentors** — title is the name, the editor body is the bio, plus *Role*,
  *Avatar initials*, *Avatar colour* and comma-separated *Skill tags*.
- **Testimonials** — title is the student name, the editor body is the quote,
  plus *Job title*, *Avatar initials*, *Avatar colour* and *Star rating*.
- **FAQs** — title is the question, the editor body is the answer.

Leave *Avatar initials* or *Avatar colour* empty and the theme derives initials
from the name and cycles the three brand colours automatically.

The packaged entries are imported as real posts on activation, so edit, reorder
or trash them like any other content. If every post of a type is removed, that
section falls back to the built-in defaults from `inc/defaults.php`.

---

## File map

```
eethal-learning/
├── style.css                    Theme header + original design CSS + WordPress styles
├── functions.php                Setup, enqueues, theme supports
├── header.php  footer.php       Chrome, nav, CTA band, modal
├── front-page.php               Landing page — loops over the section list
├── template-talent-directory.php  Page template the React app mounts into
├── build.bat                    Builds the React app (src/ → app.js)
├── index.php  page.php  single.php  archive.php  search.php  404.php
├── comments.php  searchform.php
├── inc/
│   ├── defaults.php             All fallback copy (edit here to change defaults)
│   ├── template-functions.php   eethal_opt(), helpers
│   ├── post-types.php           Courses, Mentors, Testimonials, FAQs
│   ├── meta-boxes.php           The extra fields on those types
│   ├── customizer.php           Customizer panel
│   ├── seed.php                 One-time import of the packaged content
│   ├── talent-directory.php     Talent Pool pages, roles, profiles API, sign-in
│   ├── talent-entries.php       Entry Form submissions and review
│   ├── talent-activity.php      Notifications (activity log)
│   ├── enrollments.php          Enroll Now applications and wording
│   ├── enroll-admin.php         Enroll Now settings page in wp-admin (+ the field list)
│   └── spam-guard.php           Spam checks shared by the two public forms
├── template-parts/
│   ├── sections/                One file per landing-page section
│   └── content*.php             Blog/archive/search cards
└── assets/
    ├── js/main.js               Particles, reveals, counters, FAQ, modal, mobile nav
    ├── js/customizer.js         Customizer live preview
    ├── talent-directory/        React app (src/), built app.js, styles, sign-in popup
    └── images/                  The 10 images the templates reference, with
                                 filenames slugified (spaces and parentheses
                                 removed). The project's other artwork was left
                                 in the original `asset/` and `vtnf/asset/`
                                 folders rather than bundled — upload anything
                                 you need through the Media Library instead.
```

## Talent Pool, Entry Form and Enroll Now

The React app runs on the pages that use the **Talent Directory** page template:
`/dashboard/`, `/working-professionals/`, `/students/` (the sign-in-only Talent
Pool) and the public `/entry-form/` and `/enroll/`. These pages are created
automatically, unless a page with that slug already exists. The app draws its own
header and navigation, so these pages skip the site header, footer and
landing-page CSS.

- **Access by role** — the Talent Pool needs a WordPress sign-in (email or
  username). **Talent Pool Viewer** accounts can only view; **Talent Directory
  Admin** and **Administrator** accounts can add, edit and delete profiles, review
  **New Entries**, see **Enrollments**, and get notifications. Create accounts
  under **Users → Add New**. On the rest of the site, Talent Pool links open a
  sign-in popup.
- **Data** — profiles are the **Professionals** and **Students** post types (also
  editable in the app). Entry Form submissions (`inc/talent-entries.php`) and
  Enroll Now applications (`inc/enrollments.php`) are stored as hidden post types
  and managed in the app. Both email the site admin.
- **Spam** — both public forms go through `eethal_spam_guard()` in
  `inc/spam-guard.php`: honeypot, signed form token with a minimum fill time, no links,
  hourly limit per address, and an optional Cloudflare Turnstile check. Each form also
  refuses repeats of a submission that is already in.
- **API** — everything is under `/wp-json/eethal/v1/`; see the app README for the
  route list. Uploaded photos go to the Media Library.
- **Editing the app** — the source is in `assets/talent-directory/src/` (entry
  point `app.jsx`; Enroll Now in `src/enroll/`). The page loads the compiled
  `assets/talent-directory/app.js`, and React comes from WordPress core. Rebuild
  after changes by running `build.bat` in this folder. Details are in
  [`assets/talent-directory/src/README.md`](assets/talent-directory/src/README.md).
  Styles are in `assets/talent-directory/talent-directory.css`.

## Reordering or removing landing sections

`front-page.php` renders a filterable list. From a child theme or a small
plugin:

```php
add_filter( 'eethal_front_page_sections', function ( $sections ) {
    return array_diff( $sections, array( 'marquee' ) ); // drop the marquee
} );
```

Other filters: `eethal_benefits`, `eethal_advantages`,
`eethal_about_checklist`, `eethal_outcomes_checklist` — each takes the arrays
defined in `inc/defaults.php`.

---

## What changed from the static page

- The inline `<script>` moved to `assets/js/main.js` and is enqueued in the
  footer. Modal content is now set with `textContent` rather than `innerHTML`,
  so editor-entered text cannot inject markup.
- Two sections both used `id="why-choose-us"`, and the markup had unbalanced
  `</div></section>` tags between them. The community photo section now uses
  `id="community"` and the stray tags are gone.
- Added a working mobile menu. The original simply hid the nav below 900px.
- Added focus styles, keyboard support for the FAQ and the clickable cards,
  `aria-expanded` / `aria-hidden` state, and a skip link.
- The particle canvas, reveal animations and counters respect
  `prefers-reduced-motion`.
- The hard-coded `<title>`, canonical, Open Graph and Schema.org tags were
  dropped — they pointed at one fixed URL and would be wrong on every inner
  page. WordPress emits the title via `title-tag` support; install an SEO
  plugin (Yoast, Rank Math, SEOPress) for the social and schema markup, which
  will then generate correct per-page values.

## Requirements

WordPress 6.0+, PHP 7.4+.

Font Awesome 6.4 and Google Fonts (Inter, Outfit) load from their CDNs, as in
the original page. To self-host them, drop the files into `assets/` and change
the two `wp_enqueue_style` calls at the top of `functions.php`.
