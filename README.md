# Eethal Learning

The Eethal Learning website (powered by VTNF): a WordPress site with a custom theme
that includes:

- the public landing page,
- an **Enroll Now** course application form,
- a **Talent Pool**: a sign-in-only directory of alumni (working professionals and
  students), with an admin area for managing profiles and reviewing submissions.

Everything is in one theme: [`wp-content/themes/eethal-learning`](wp-content/themes/eethal-learning).
No extra plugins are required.

---

## Features

### Landing page (`/`)
- Sections: hero, benefits, marquee, about, courses, why choose us, community photo,
  student outcomes, mentors, alumni stories, FAQ, and a closing call to action.
- All text, images and lists are edited in **Appearance → Customize → Eethal Front Page**.
  Each section can be hidden with its **Show this section** checkbox.
- Courses, Mentors, Testimonials and FAQs are their own post types in the admin menu.
- WhatsApp click-to-chat button in the header.
- Responsive. On phones the header is compact, with the logo, the WhatsApp number on one
  line, and the menu button.

### Enroll Now form (`/enroll/`)
- Replaces the old Batch-8 Google Form. Every **Enroll Now** / course button on the site
  opens it.
- Questions: name, email, mobile, date of birth, district, referred by, current status,
  degree (with an "Other" option), college, and year passed out.
- **All wording is edited in WordPress**: **wp-admin → Enroll Now** in the admin menu
  (the same fields are also under **Customize → Eethal Front Page → Enroll Now Form**).
  This covers the heading, welcome text, each question's label and hint, the choices, the
  button and the thank-you message. Write `{batch}` anywhere to show the batch name.
- Each application is saved with the current **batch** (e.g. "Batch 8"). Change the batch
  on the same page when a new one opens.
- The site admin gets an email for every application.
- Spam protection (see [Spam protection](#spam-protection) below).

### Talent Pool (`/dashboard/`, `/working-professionals/`, `/students/`)
- A directory of alumni ID cards with search and filters (district, experience,
  designation, company, education, marital status, year of graduation, and so on).
- **Sign-in only.** Links to the Talent Pool open a sign-in popup on the rest of the site.
- What you can do depends on your WordPress role:

  | Role | Can do |
  |---|---|
  | **Talent Pool Viewer** | Browse and search profiles (view only) |
  | **Talent Directory Admin** / **Administrator** | Everything below |

- Admins also get:
  - **Add, edit and delete** profiles, including photo upload or a Google Drive photo link.
  - **New Entries**: review submissions from the public Entry Form (`/entry-form/`).
    Approving one creates the profile; rejecting it only marks it rejected. Deleted
    entries are soft-deleted (kept in the database, hidden from the list).
  - **Enrollments**: every Enroll Now application, with search, filters (batch,
    status, degree, district), a detail view, delete, and **Export CSV** (opens in Excel).
  - **Notifications** (the bell): new entries, new applications and profile changes.
    Viewers don't see it.

### Public Entry Form (`/entry-form/`)
- People submit their own Working Professional or Student details. The submissions wait
  in **New Entries** for an admin to approve or reject them.
- The site admin gets an email for every submission.

### Spam protection
Both public forms (Enroll Now and the Entry Form) check every submission on the server
(`inc/spam-guard.php`) before saving it:

| Check | Stops |
|---|---|
| Hidden "website" box (honeypot) | Simple bots that fill every box. They see a fake "thank you" and nothing is saved. |
| Signed form token | Bots posting straight to the API without opening the page, and forms sent back within 4 seconds of the page loading |
| No web links in answers | Link spam (a Google Drive photo link on the Entry Form is still allowed) |
| One per person | A second Enroll Now application from the same email or mobile in the same batch, or a second entry while the first is still waiting for review |
| Hourly limit | More than 5 submissions an hour from one connection |
| "I'm not a robot" check (optional) | Smarter bots. Uses the free Cloudflare Turnstile and is off until you add its keys. |

**To turn on the robot check:**
1. Sign in at https://dash.cloudflare.com, open **Turnstile** and add a widget for the
   site's domain.
2. Paste its **site key** and **secret key** into **wp-admin → Enroll Now → Form Spam
   Protection** and click **Save Changes**.

The box then appears above the submit button on both forms. The messages people see when
a check stops them are edited in the same place.

---

## Local setup (Windows + XAMPP)

### Requirements
- [XAMPP](https://www.apachefriends.org/) with PHP 8.0+ and MySQL/MariaDB. The site runs
  on WordPress 7.1 and PHP 8.0.
- [Git](https://git-scm.com/).
- [Node.js](https://nodejs.org/), only if you'll change the Talent Pool or Enroll Now
  React code.

### Steps

1. **Get the code** into XAMPP's web folder:
   ```
   cd C:\xampp\htdocs
   git clone https://github.com/kananmhn/eethallearning.git eethal-learning
   ```
2. **Create a database.** Start Apache and MySQL in the XAMPP Control Panel, open
   http://localhost/phpmyadmin and create a database named `eethal_learning`
   (collation `utf8mb4_unicode_ci`).
3. **Configure WordPress.** `wp-config.php` isn't in Git. Copy `wp-config-sample.php` to
   `wp-config.php` and set:
   ```php
   define( 'DB_NAME', 'eethal_learning' );
   define( 'DB_USER', 'root' );
   define( 'DB_PASSWORD', '' );
   define( 'DB_HOST', 'localhost' );
   ```
   Also replace the "put your unique phrase here" keys with fresh ones from
   https://api.wordpress.org/secret-key/1.1/salt/.
4. **Install WordPress.** Open http://localhost/eethal-learning, then choose the site
   title and create your administrator account.
5. **Activate the theme.** In **Appearance → Themes**, activate **Eethal Learning**.
   On first activation the theme:
   - imports the default courses, mentors, testimonials, FAQs, images, logo and
     Primary menu (it never overwrites existing content);
   - creates the pages Dashboard, Working Professionals, Students, Entry Form and
     Enroll Now;
   - adds the **Talent Directory Admin** and **Talent Pool Viewer** roles.
6. **Set permalinks.** In **Settings → Permalinks**, choose **Post name** and save.
   The Talent Pool and Enroll Now pages need clean URLs such as `/dashboard/`.
7. **Create Talent Pool accounts** under **Users → Add New** with the role
   **Talent Pool Viewer** (view only) or **Talent Directory Admin** (full access).
   Administrators already have full access.

The site is now at http://localhost/eethal-learning.

### Email
The admin emails for new entries and applications go to the address in
**Settings → General → Administration Email Address**. XAMPP can't send mail by itself,
so install an SMTP plugin (for example *WP Mail SMTP*) and connect it to a mailbox,
such as a Gmail app password. The live server needs this too unless its host already
sends mail.

### Moving content between machines
The database and `wp-content/uploads/` aren't in Git. To copy real content, either:
- export the database in phpMyAdmin and copy the `uploads` folder, or
- use a migration plugin such as *All-in-One WP Migration*.

After importing on a different URL, update the site URL (**Settings → General**, or a
search-and-replace tool) and re-save **Settings → Permalinks**.

---

### Building the React app
The browser loads the compiled `assets/talent-directory/app.js`; the source is in
`assets/talent-directory/src/`. After changing anything in `src/`, run:

```
C:\xampp\htdocs\eethal-learning\wp-content\themes\eethal-learning\build.bat
```

Add `watch` at the end to rebuild on every save. The build downloads esbuild via `npx`
the first time; you don't need `npm install`. PHP and CSS changes need no build.
Commit the rebuilt `app.js` together with your source changes.

### More detail
- [Theme README](wp-content/themes/eethal-learning/README.md): every Customizer
  section, the content types, and the theme's file map.
- [App README](wp-content/themes/eethal-learning/assets/talent-directory/src/README.md):
  the React app's files, settings, the REST API (`/wp-json/eethal/v1/`), and how to add
  a profile field.
