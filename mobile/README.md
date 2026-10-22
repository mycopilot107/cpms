# Best CPMS Mobile (Flutter)

A field-staff companion app for the Best CPMS construction platform. Workers sign
in, see the tasks assigned to them, and report progress — status, percent
complete, and hours worked — straight from their phone.

It talks to the same Laravel backend as the web app over a small JSON API
(`/api/*`), authenticated with a bearer token. All data is tenant-scoped on the
server, so a user only ever sees their own company's tasks.

## What's here

```
lib/
  config.dart              API base URL (edit this)
  main.dart                app entry + auth gate
  theme.dart               indigo theme + status/priority colors
  models/                  User, Task (mirror the API resources)
  services/
    api_client.dart        HTTP + JSON + error handling
    auth_service.dart      login / logout / token persistence
    task_service.dart      list + update tasks
  screens/                 login, my-tasks list, task detail/update
  widgets/pills.dart       status & priority chips
```

State management is `provider` (ChangeNotifier). Dependencies are intentionally
minimal: `http`, `provider`, `shared_preferences`, `intl`.

## Prerequisites

- Flutter SDK 3.x (Dart 3). Check with `flutter --version`.
- The Best CPMS Laravel API running and reachable from your device/emulator.

## First-time setup

This folder contains only the Dart sources and `pubspec.yaml` — the generated
platform folders (`android/`, `ios/`, …) are not committed. Create them once:

```sh
cd mobile
flutter create .        # generates android/ios/etc without touching lib/
flutter pub get
```

## Point the app at your API

Start the backend so it listens on all interfaces:

```sh
# from the Laravel project root
php artisan serve --host 0.0.0.0 --port 8000
```

Then set the base URL for your run target. The default in `lib/config.dart`
is the Android emulator address:

| Run target        | API base URL                          |
|-------------------|---------------------------------------|
| Android emulator  | `http://10.0.2.2:8000/api` (default)  |
| iOS simulator     | `http://127.0.0.1:8000/api`           |
| Physical phone    | `http://<your-computer-LAN-IP>:8000/api` |

You can override it at run time instead of editing the file:

```sh
flutter run --dart-define=API_BASE_URL=http://192.168.1.20:8000/api
```

## Run it

```sh
flutter run
```

Sign in with a seeded field-worker account (after `php artisan migrate:fresh
--seed` on the backend):

```
worker@demo.test  /  password
```

`engineer@demo.test` and `supervisor@demo.test` (same password) also work and
have tasks assigned.

## Notes & limits (MVP)

- Auth is a bearer token stored with `shared_preferences`; it survives restarts
  and is revoked server-side on logout.
- The app is deliberately read-mostly: workers can change **status, progress,
  and actual hours** only. Reassigning, renaming, or scheduling stays on the web
  where planners work.
- Photos, comments, and offline queueing are not implemented yet — see the API
  doc for where they would slot in.
