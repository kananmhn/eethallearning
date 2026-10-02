# Talent Directory app (React)

This folder is the source code of the Talent Directory app. `app.jsx` is the entry
point; it imports the other files (see [Files](#files)). The app runs on these pages:

- Dashboard
- Working Professionals
- Students
- Entry Form
- Enroll Now (`/enroll/`, the course application form)

Browsers can't run `.jsx` directly, so it has to be **built** into
`../app.js`. The build bundles `app.jsx` and every file it imports into that one file,
which is what WordPress loads on the pages.

> Edit the files in `src/`, never `app.js`. `app.js` is overwritten on every build.

## Requirements

- [Node.js](https://nodejs.org/) (any recent version). `npx` comes with it.

You don't need `npm install` or a `package.json`. The first time you build, `npx`
downloads esbuild by itself. React is not bundled into the app because WordPress
core already provides it (the `react` and `react-dom` scripts).

## Build

The easy way is to run `build.bat` from the theme folder. It works from any folder and in any terminal:

```
C:\xampp\htdocs\eethal-learning\wp-content\themes\eethal-learning\build.bat
```

To rebuild automatically every time you save `app.jsx`, add `watch` to the end of that command
(press Ctrl+C to stop). You can also double-click `build.bat` in File Explorer.

### Common errors

- **`Could not resolve "assets/talent-directory/src/app.jsx"`**: you're in the wrong folder.
  The command below only works from the theme folder. Use `build.bat`, or `cd` to the theme folder first.
- **`npx.ps1 cannot be loaded because running scripts is disabled`**: PowerShell is blocking `npx`.
  Use `build.bat`, or type `npx.cmd` instead of `npx`.

### Manual command

Run this from the **theme folder**:

```
cd C:\xampp\htdocs\eethal-learning\wp-content\themes\eethal-learning

npx esbuild assets/talent-directory/src/app.jsx --bundle --loader:.jsx=jsx --jsx-factory=React.createElement --jsx-fragment=React.Fragment --format=iife --target=es2018 --minify --charset=utf8 --outfile=assets/talent-directory/app.js
```

When the build works, it prints the file size and `Done in ..ms`. You can ignore
any Node `ExperimentalWarning` line.

Then press **Ctrl+F5** in the browser to see your change. WordPress versions
`app.js` by its modified time, so browsers pick up the new file.

### Rebuild automatically while you work

Add `--watch` to the end of the command:

```
npx esbuild assets/talent-directory/src/app.jsx --bundle --loader:.jsx=jsx --jsx-factory=React.createElement --jsx-fragment=React.Fragment --format=iife --target=es2018 --minify --charset=utf8 --outfile=assets/talent-directory/app.js --watch
```

esbuild then rebuilds `app.js` every time you save a file in `src/`. Press **Ctrl+C** to stop it.

### What doesn't need a build

- `../talent-directory.css` (styles)
- Any PHP file in the theme

Save these and refresh the page.

## Files

| File | What it is |
|---|---|
| `src/app.jsx` | Entry point: the directory app (Dashboard, profile lists, New Entries, sign-in) and the Entry Form |
| `src/core.js` | `CONFIG` (settings from WordPress) and the `api()` REST helper |
| `src/icons.jsx` | Line icons (`I.Home`, `I.Mail`, ...) |
| `src/shared.jsx` | Pieces used in more than one place: logo, avatar, form field, filters, dates |
| `src/enroll/` | Enroll Now: `EnrollFormApp.jsx` (the public page), `EnrollForm.jsx` (the form), `EnrollmentsPage.jsx` (admin list and detail), `csv.js` (Export CSV), `validate.js` (form checks), `texts.js` (the WordPress-authored wording) |
| `app.js` | Built app. Don't edit it by hand. |
| `talent-directory.css` | App styles |
| `../../inc/talent-directory.php` | Pages, profile post types, and the REST API for profiles and sign-in |
| `../../inc/talent-entries.php` | Entry Form submissions, the admin email, and approving or rejecting entries |
| `../../inc/enrollments.php` | Enroll Now applications: saving, the admin email, and the Enrollments list |
| `../../template-talent-directory.php` | Page template that the app mounts into |

## How the app gets its settings

PHP passes settings to the app as `window.EETHAL_TD` (see `eethal_td_enqueue()`):

| Setting | What it holds |
|---|---|
| `restUrl` | Base URL of the REST API |
| `nonce` | REST nonce |
| `view` | Which page this is: `dashboard`, `professionals`, `students`, `entry` or `enroll` |
| `pages` | URL of each page |
| `user` | The signed-in admin, or `null` for visitors |
| `enroll` | All Enroll Now wording: heading, intro, questions (`label` and `hint`), choices, button and thank-you text. Authored in Customizer → Eethal Front Page → Enroll Now Form; built by `eethal_enroll_texts()`. Don't hardcode this text in `src/enroll/`. |

On the Entry Form page (`view === "entry"`), the app renders `EntryFormApp`; on the
Enroll Now page (`view === "enroll"`), `EnrollFormApp`. Every other page renders `App`.

## REST API (`/wp-json/eethal/v1/`)

| Method & route | Who can call it | Purpose |
|---|---|---|
| `GET professionals`, `GET students` | Anyone | List profiles |
| `POST`, `PUT`, `DELETE professionals[/id]`, `students[/id]` | Admin | Add, edit or delete a profile |
| `POST entries` | Anyone | Submit the Entry Form (also emails the admin) |
| `GET entries` | Admin | List entries for New Entries |
| `POST entries/{id}/approve` | Admin | Create the profile from the entry |
| `POST entries/{id}/reject` | Admin | Mark the entry as rejected |
| `DELETE entries/{id}` | Admin | Soft delete a reviewed entry. It is kept with the `_eethal_entry_deleted = 1` flag. |
| `POST entries/{id}/restore` | Admin | Undo a soft delete |
| `POST enrollments` | Anyone | Submit the Enroll Now form (also emails the admin) |
| `GET enrollments` | Admin | List applications for Enrollments |
| `DELETE enrollments/{id}` | Admin | Move an application to the trash |
| `POST auth/login`, `POST auth/logout` | Anyone | Sign in and sign out. Talent Pool Viewer role = view only; Talent Directory Admin / Administrator = full access |
| `GET notifications` | Anyone | The notification bell. Visitors get public events only; admins get everything. The log is kept in the `eethal_td_activity` option (`inc/talent-activity.php`). |

"Admin" means a user with the `manage_talent_directory` capability. Administrators and
the Talent Directory Admin role have it.

## Adding a profile field

Use Batch No (`batch`) as the example to copy:

1. Add the field name to `eethal_td_fields()` in `inc/talent-directory.php`. It is
   stored as post meta `_eethal_<field>`.
2. Add it to the WP-admin meta box in `inc/meta-boxes.php`.
3. In `app.jsx`, add it to these places:
   - `blankPro` and `blankStu` in `ProfileForm`
   - a `<Field>` in the form
   - a `<DetailField>` in `ProfileDetailPage` and `EntryDetail`
   - `SEARCH_FIELDS`, if it should be searchable
4. Add it to the email rows in `eethal_td_entry_email_html()` in `inc/talent-entries.php`.
5. Build.
