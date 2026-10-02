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
| Closing CTA (text, both buttons and links, labels) + footer credit | **Eethal Front Page → Call to Action & Footer** |
| Favicon | **Customize → Site Identity → Site Icon** |

List fields take one item per line. Icon lists (benefits, advantages) use
`icon | text`, e.g. `🎓 | 10+ Years Exp Trainers`.

On first activation `inc/seed.php` imports the packaged content once: the
course, mentor, testimonial and FAQ entries, the section images and logo (into
the Media Library, wired to their Customizer fields) and a Primary menu. It
never overwrites existing content.

The **enrolment link** set under *Contact & Links* is reused by the hero button,
the CTA button, and any course card that has no link of its own — so changing
the Google Form URL in one place updates the whole site.

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
├── index.php  page.php  single.php  archive.php  search.php  404.php
├── comments.php  searchform.php
├── inc/
│   ├── defaults.php             All fallback copy (edit here to change defaults)
│   ├── template-functions.php   eethal_opt(), helpers
│   ├── post-types.php           Courses, Mentors, Testimonials, FAQs
│   ├── meta-boxes.php           The extra fields on those types
│   └── customizer.php           Customizer panel
├── template-parts/
│   ├── sections/                One file per landing-page section
│   └── content*.php             Blog/archive/search cards
└── assets/
    ├── js/main.js               Particles, reveals, counters, FAQ, modal, mobile nav
    ├── js/customizer.js         Customizer live preview
    └── images/                  The 10 images the templates reference, with
                                 filenames slugified (spaces and parentheses
                                 removed). The project's other artwork was left
                                 in the original `asset/` and `vtnf/asset/`
                                 folders rather than bundled — upload anything
                                 you need through the Media Library instead.
```

## Talent Directory (Dashboard, Working Professionals, Students)

The directory app lives on three pages that use the **Talent Directory** page
template: `/dashboard/`, `/working-professionals/` and `/students/`. They are
created automatically (unless a page with that slug already exists). The app
draws its own header, navigation and sign-in, so these pages skip the site
header, footer and landing-page CSS.

- **Data** — profiles are the **Professionals** and **Students** post types in
  the admin menu (title = name, fields in the details box). Admins can also add,
  edit and delete them from the app itself.
- **Sign in** — the app's *Sign In* button accepts a WordPress email (or
  username) and password. Only users with the `manage_talent_directory`
  capability can sign in: Administrators and the **Talent Directory Admin**
  role. Add a user with that role under **Users → Add New** to give someone
  access. Sign-in persists across page loads (WordPress login cookie).
- **API** — `inc/talent-directory.php` registers `/wp-json/eethal/v1/`
  `professionals`, `students` (GET public; POST / PUT / DELETE for directory
  admins), `auth/login` and `auth/logout`. Uploaded photos go to the Media
  Library.
- **Editing the app** — the source is
  `assets/talent-directory/src/app.jsx`; the page loads the compiled
  `assets/talent-directory/app.js` (React comes from WordPress core). Rebuild
  after changes:

  ```
  npx esbuild assets/talent-directory/src/app.jsx --loader:.jsx=jsx --jsx-factory=React.createElement --jsx-fragment=React.Fragment --format=iife --target=es2018 --minify --charset=utf8 --outfile=assets/talent-directory/app.js
  ```

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
