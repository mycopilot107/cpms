# Best CPMS Mobile — running the field app

The Flutter app for field staff lives in [`/mobile`](../mobile). This page is
the quick "get it running end-to-end" guide; see
[`mobile/README.md`](../mobile/README.md) for the code layout and
[`docs/API.md`](API.md) for the endpoint contract.

> **Shipping a build to field staff?** This page is for running the app in
> development. To produce an installable **APK** that workers download from the
> website, see [`docs/MOBILE-BUILD.md`](MOBILE-BUILD.md).

## 1. Backend

From the Laravel project root:

```sh
php artisan migrate:fresh --seed      # creates the demo company + users + tasks
php artisan serve --host 0.0.0.0 --port 8000
```

`--host 0.0.0.0` matters: it lets an emulator or a real phone reach the server,
not just `localhost`.

The `api_tokens` table is created by the migration
`2026_09_24_000000_create_api_tokens_table.php` (included in `migrate:fresh`).

## 2. App

```sh
cd mobile
flutter create .        # one-time: generates android/ios/etc (won't touch lib/)
flutter pub get
flutter run
```

## 3. Point the app at the server

Set the API base URL to match how you run it (default is the Android emulator):

| Run target        | API base URL                             |
|-------------------|------------------------------------------|
| Android emulator  | `http://10.0.2.2:8000/api` (default)     |
| iOS simulator     | `http://127.0.0.1:8000/api`              |
| Physical phone    | `http://<computer-LAN-IP>:8000/api`      |

Edit `mobile/lib/config.dart`, or override without editing:

```sh
flutter run --dart-define=API_BASE_URL=http://192.168.1.20:8000/api
```

## 4. Log in

Seeded field-worker accounts (password `password` for all):

- `worker@demo.test`
- `engineer@demo.test`
- `supervisor@demo.test`

They belong to **ABC Construction** and have tasks assigned, so the "My tasks"
list is populated. Change a task's status/progress/hours and the update is
written straight back to the same database the web app uses.

## 5. What the app can do

View assigned tasks (Open / All filter, pull-to-refresh) and update **status,
percent complete, and actual hours** on a task. It cannot reassign, rename, or
reschedule — those stay on the web app for planners. Auth is a bearer token that
persists across restarts and is revoked on logout.

## Troubleshooting

- **"Cannot reach the server."** — The base URL is wrong for your target (see
  the table), or `php artisan serve` isn't bound to `0.0.0.0`.
- **Immediately bounced to login** — The token was rejected (expired/revoked) or
  the account was set inactive on the web side. Sign in again.
- **Empty list** — You're signed in as someone with no assigned tasks; try
  `worker@demo.test` or switch the filter to "All".
