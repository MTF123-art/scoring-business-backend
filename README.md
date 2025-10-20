# Scoring Business Backend – API Documentation

This document describes all available API endpoints, their authentication requirements, request/response formats, and example payloads.

## Authentication

Most endpoints require authentication using Laravel Sanctum.

-   Obtain a token via `/api/login`.
-   Send the token in the `Authorization` header: `Bearer <token>`.

Standard JSON envelope:

-   Success: `{ "success": true, "data": <payload>, "message": "..." }`
-   Error: `{ "success": false, "message": "...", "error": "(optional details)" }`

---

## Endpoints

### 1) Home Screen Data

GET `/api/home` (Auth required)

Returns aggregated data for the mobile home screen: today score, rank, platform connections, weekly comparison, and daily score series from start-of-week to today.

Response example:

```json
{
    "success": true,
    "data": {
        "user": { "id": 123, "name": "Linkebin" },
        "generatedAt": "2025-10-20T07:30:00Z",
        "platforms": [
            {
                "platform": "instagram",
                "connected": true,
                "account": "@brand.ig"
            },
            { "platform": "facebook", "connected": false, "account": null }
        ],
        "today": {
            "score": 97,
            "perPlatform": [
                { "platform": "instagram", "score": 70 },
                { "platform": "facebook", "score": 80 }
            ],
            "rank": { "position": 12, "total": 100 }
        },
        "weeklyComparison": {
            "lastWeekAvgScore": 87,
            "thisWeekAvgScore": 93,
            "deltaPercent": 5.6
        },
        "charts": {
            "dailyScoreSeries": [
                { "date": "2025-10-14", "value": 90 },
                { "date": "2025-10-15", "value": 97 }
            ]
        }
    },
    "message": "home"
}
```

---

### 2) Register

POST `/api/register`

Body (JSON):

```json
{
    "name": "John",
    "email": "john@example.com",
    "password": "secret",
    "password_confirmation": "secret"
}
```

Responses:

-   200 success: `{ "success": true, "data": { "user": { ... } }, "message": "pendaftaran berhasil" }`
-   422 validation error or 500 on server error

---

### 3) Login

POST `/api/login`

Body (JSON):

```json
{ "email": "john@example.com", "password": "secret" }
```

Responses:

-   200 success: `{ "success": true, "data": { "user": { ... }, "sanctum_token": "<token>" }, "message": "login berhasil" }`
-   400/401 invalid credentials; 422 validation error

---

### 4) Logout

POST `/api/logout` (Auth required)

Response: `{ "success": true, "data": null, "message": "logout berhasil" }`

---

### 5) Get Today Score

GET `/api/score` (Auth required)

Returns today’s `Score` for the current user. If missing, it will be calculated.

Response example:

```json
{
    "success": true,
    "data": {
        "business_id": 123,
        "date": "2025-10-20",
        "instagram_score": 70,
        "facebook_score": 80,
        "final_score": 97
    },
    "message": "score (cached) | score berhasil dihitung"
}
```

---

### 6) Leaderboard

GET `/api/leaderboard/{period}` (Auth required)

Path params:

-   `period`: `daily` or `weekly`

Behavior:

-   `daily`: orders by today’s `final_score`.
-   `weekly`: averages `final_score` from start-of-week to today and orders by average.

Response example:

```json
{
    "success": true,
    "data": [
        { "name": "Alice", "score": 98.5, "avatar_url": "https://..." },
        { "name": "Bob", "score": 95.0, "avatar_url": null }
    ],
    "message": "leaderboard weekly"
}
```

Errors:

-   400 if `period` is not one of `daily` or `weekly`.

---

### 7) Instagram

GET `/api/instagram/connect` (Auth required)

-   Returns a URL for OAuth login.

```json
{
    "success": true,
    "data": { "url": "https://facebook.com/..." },
    "message": "url login instagram berhasil dibuat"
}
```

GET `/api/instagram/callback`

-   Handles the OAuth callback; returns an HTML page indicating connection result.

GET `/api/instagram/metrics` (Auth required)

-   Fetches or stores today’s metrics for the connected Instagram account.

```json
{
    "success": true,
    "data": { "followers": 1000, "...": 0 },
    "message": "data metric instagram (cached) | berhasil mengambil & menyimpan metric instagram"
}
```

DELETE `/api/instagram/disconnect` (Auth required)

-   Disconnects the current user’s Instagram account.

```json
{
    "success": true,
    "data": null,
    "message": "akun instagram berhasil diputuskan"
}
```

---

### 8) Facebook

GET `/api/facebook/connect` (Auth required)

-   Returns a URL for OAuth login.

GET `/api/facebook/callback`

-   Handles the OAuth callback; returns an HTML page indicating connection result.

GET `/api/facebook/metrics` (Auth required)

-   Fetches or stores today’s metrics for the connected Facebook Page.

DELETE `/api/facebook/disconnect` (Auth required)

-   Disconnects the current user’s Facebook account.

```json
{
    "success": true,
    "data": null,
    "message": "akun facebook berhasil diputuskan"
}
```

---

### 9) Profile

GET `/api/profile` (Auth required)

-   Returns user profile; includes `avatar_url` via helper `user_avatar_url()`.

POST `/api/profile` (Auth required)

-   Multipart/form-data supported for `avatar`.
-   Fields (all optional): `name`, `email`, `avatar`(image)

```bash
Content-Type: multipart/form-data
```

Response: `{ "success": true, "data": { ...user }, "message": "profil berhasil diperbarui" }`

POST `/api/profile/change-password` (Auth required)

-   Body (JSON): `{ "current_password": "...", "new_password": "...", "new_password_confirmation": "..." }`

```json
{ "success": true, "data": null, "message": "password berhasil diubah" }
```

GET `/api/user/avatar/{id}`

-   Returns the avatar image file for a user id. Responds with 404 if not found.

---

## Scoring

Formula used for platform scoring:

```
score = 0.4*ER + 0.3*RR + 0.3*EPP
```

Normalized ranges:

-   ER: 0..10
-   RR: 0..5
-   EPP: 0..500

Final daily score is the average of platform scores (Instagram, Facebook).

---

## Conventions

-   All timestamps in responses are ISO 8601 (UTC) unless noted.
-   Week boundaries use Laravel/Carbon `startOfWeek()` based on app timezone.
-   Error responses may include an additional `error` detail string for debugging.
