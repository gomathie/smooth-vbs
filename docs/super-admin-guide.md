# Smooth VBS — Super Admin Guide

This guide covers everything a super admin needs to operate the platform: creating accounts, onboarding organizations, managing white-label branding, and setting up custom domains.

---

## Signing In

Go to your platform URL (e.g. `https://app.smoothvbs.com/login`) and sign in with your super admin credentials.

Super admin accounts are created via the server command line — they cannot be self-registered through the web interface. See [Creating a Super Admin](#creating-a-super-admin) below.

Once signed in, you will see an **Organizations** link in the left sidebar. This is only visible to super admins.

---

## Creating a Super Admin

Super admin accounts must be created directly on the server using the Artisan command. This is intentional — it prevents unauthorized escalation of privileges through the web UI.

**Step 1 — SSH into your server**

**Step 2 — Navigate to the application folder**

```bash
cd /var/www/smoothvbs
```

**Step 3 — Run the create command**

```bash
php artisan admin:create
```

You will be prompted for:

- Full name
- Email address
- Password (minimum 6 characters)
- Password confirmation

The command validates the email is unique and hashes the password before saving. If no organization exists yet, it creates a default "Platform Headquarters" organization automatically.

**Example session:**

```
Create Super Admin
─────────────────
 Full name: Platform Admin
 Email address: admin@yourcompany.com
 Password (min 8 characters):
 Confirm password:

 Super admin created successfully.
 ┌──────────────┬─────────────────────────┐
 │ Field        │ Value                   │
 ├──────────────┼─────────────────────────┤
 │ Name         │ Platform Admin          │
 │ Email        │ admin@yourcompany.com   │
 │ Role         │ super_admin             │
 │ Organization │ Platform Headquarters   │
 └──────────────┴─────────────────────────┘
```

> **Security note:** Store super admin credentials in a password manager. There is no password reset via email in the current version — if credentials are lost, a new account must be created via the server command.

---

## Adding a New Organization

When a new client joins the platform, you create an organization for them.

**Steps:**

1. Sign in as super admin
2. Click **Organizations** in the left sidebar
3. Click **Add Organization** (top right)
4. Fill in the form:

| Field        | Notes                                                     |
| ------------ | --------------------------------------------------------- |
| **Name**     | The organization's full name (e.g. "Acme Transport Ltd.") |
| **Timezone** | Select the timezone where most of their operations occur  |

5. Optionally fill in the **Initial Administrator** section to create their first Organization Admin account at the same time:

| Field             | Notes                                                     |
| ----------------- | --------------------------------------------------------- |
| **Full Name**     | The name of their admin user                              |
| **Email Address** | Their login email                                         |
| **Password**      | Temporary password — ask them to change it on first login |

6. Click **Create Organization**

The new organization appears in the Organizations list with user and vehicle counts.

> **Tip:** If you skip the initial admin, you can add users later via the Users section while logged in — or ask the organization admin to do it themselves once their account is created.

---

## Adding an Organization Admin for an Existing Organization

If an organization already exists but needs a new admin user added:

1. Sign in as super admin
2. Go to **Organizations** → find the organization → click **Edit**
3. Note the organization name
4. Go to **Users** in the sidebar
5. Click **Add User**
6. Fill in the user's details and set **Role** to `Organization Admin`
7. Click **Save**

> **Note:** The Users list currently shows users from the super admin's own organization. To add a user to a different organization, this is done through the **Initial Administrator** section when creating or editing an organization. Alternatively, log in as that organization's admin and add users from there.

---

## Setting Up White-Label Branding

White labeling allows an organization's users to see a custom brand name, logo, color, and domain instead of the default Smooth VBS branding.

**What to collect from the client before starting:**

| Item          | What to ask for                                                                                  |
| ------------- | ------------------------------------------------------------------------------------------------ |
| Brand name    | The name they want shown in the app (e.g. "Acme Fleet Manager")                                  |
| Logo          | A publicly accessible URL to their logo (PNG or SVG, ideally square)                             |
| Primary color | Their brand hex color code (e.g. `#e63946`) — available from their designer or brand style guide |
| Custom domain | The subdomain or domain they want to use (e.g. `fleet.acme.com`)                                 |

**Steps to apply branding:**

1. Sign in as super admin
2. Click **Organizations** in the sidebar
3. Find the organization and click **Edit**
4. Scroll down to the **White Label** section
5. Fill in the fields:
    - **Brand Name** — shown in the sidebar and page titles for users of this org
    - **Logo URL** — paste the public URL to their logo image
    - **Primary Color** — use the color picker or type the hex code directly
    - **Custom Domain** — the domain they will use (without `https://`)
6. Click **Save White Label**

Changes take effect immediately for all users in that organization.

---

## Setting Up a Custom Domain (with SSL)

After saving the custom domain in the system, you need to configure the server to accept traffic on that domain.

### Step 1 — Client configures DNS

The client's IT team adds a DNS CNAME record pointing their domain to your server:

```
Type:  CNAME
Name:  fleet
Value: app.smoothvbs.com   (your server's domain or IP)
TTL:   3600
```

DNS changes can take up to 24 hours to propagate globally, though often it is much faster.

### Step 2 — Add Apache VirtualHost

SSH into your server and create a new Apache site configuration:

```bash
nano /etc/apache2/sites-available/fleet-acme.conf
```

Paste the following (replace `fleet.acme.com` and the document root path with your values):

```apache
<VirtualHost *:443>
    ServerName fleet.acme.com

    DocumentRoot /var/www/smoothvbs/public

    SSLEngine on
    SSLCertificateFile      /etc/letsencrypt/live/fleet.acme.com/fullchain.pem
    SSLCertificateKeyFile   /etc/letsencrypt/live/fleet.acme.com/privkey.pem

    <Directory /var/www/smoothvbs/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/fleet-acme-error.log
    CustomLog ${APACHE_LOG_DIR}/fleet-acme-access.log combined
</VirtualHost>

<VirtualHost *:80>
    ServerName fleet.acme.com
    Redirect permanent / https://fleet.acme.com/
</VirtualHost>
```

### Step 3 — Obtain the SSL certificate

```bash
certbot --apache -d fleet.acme.com
```

Certbot will automatically update the Apache config with the SSL certificate paths and set up auto-renewal.

### Step 4 — Enable the site and reload Apache

```bash
a2ensite fleet-acme
systemctl reload apache2
```

### Step 5 — Test

Visit `https://fleet.acme.com/login` in a browser. You should see the organization's brand name, logo, and colors on the login page.

---

## Rebranding an Existing Organization

When a client updates their branding (new logo, new colors, name change):

1. Collect the updated assets from the client (see the table in [White-Label Branding](#setting-up-white-label-branding))
2. Sign in as super admin
3. Go to **Organizations** → **Edit** on their organization
4. Scroll to **White Label** and update the relevant fields
5. Click **Save White Label**

Changes are live immediately — no server restart or asset rebuild required.

If they are also changing their custom domain:

- Update the **Custom Domain** field in the White Label section
- Add a new Apache VirtualHost for the new domain (Step 2 above)
- Run Certbot for the new domain (Step 3 above)
- Ask the client to update their DNS record to point the new domain to your server

---

## Editing an Organization's Details

To change an organization's name or timezone:

1. Go to **Organizations** → **Edit**
2. Update the **Name** or **Timezone** in the top form
3. Click **Save Changes**

> **Note:** Changing the name does not change the organization's URL slug, which was set at creation time.

---

## Deleting an Organization

An organization can only be deleted if it has **no users and no vehicles**. The Delete button appears on the Edit page only when both counts are zero.

To delete a populated organization:

1. Ensure all vehicles are removed first
2. Ensure all users are deactivated or removed first
3. The Delete button will then appear — click it and confirm

> **Caution:** Deletion is permanent. All organization data (GPS integrations, bookings, audit logs) will be lost. Consider deactivating all users instead of deleting if you may need to restore the organization later.

---

## Managing the GPS Scheduler

GPS locations are synced automatically every 3 minutes via the Laravel scheduler. The scheduler must be running for this to work.

**Check if the cron is set up:**

```bash
crontab -l
```

You should see a line like:

```
* * * * * php /var/www/smoothvbs/artisan schedule:run >> /dev/null 2>&1
```

**If it is missing, add it:**

```bash
crontab -e
```

Add the line above, save, and exit.

**To trigger a manual sync immediately (for testing):**

```bash
php artisan gps:sync
# or for a specific organization:
php artisan gps:sync --organization=1
```

---

## Quick Reference

| Task                           | Where                                                |
| ------------------------------ | ---------------------------------------------------- |
| Create super admin             | Server: `php artisan admin:create`                   |
| Add organization               | Organizations → Add Organization                     |
| Add org admin                  | Organizations → Edit → Initial Administrator section |
| Set white-label branding       | Organizations → Edit → White Label section           |
| Set up custom domain SSL       | Server: `certbot --apache -d domain.com`             |
| Manual GPS sync                | Server: `php artisan gps:sync`                       |
| Clear view cache after changes | Server: `php artisan view:clear`                     |
