
## Authentication Endpoints

| Method | Endpoint | Keterangan | Auth |
|---|---|---|---|
| POST | `/auth/register` | Registrasi pengguna baru | Tidak |
| POST | `/auth/login` | Login dan memperoleh token | Tidak |
| POST | `/auth/logout` | Mencabut token aktif | Ya |

Rate limit:
- Register: 3 request per menit.
- Login: 5 request per menit.

### Register

Request:

```json
{
  "name": "Budi",
  "email": "budi@example.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

Response berhasil (`201 Created`):

```json
{
  "message": "Registrasi Berhasil",
  "user": {
    "id": 1,
    "name": "Budi",
    "email": "budi@example.com"
  }
}
```

Registrasi tidak langsung memberikan token. Pengguna harus melakukan login setelah berhasil mendaftar.

Validasi:
- `name`: wajib, string, maksimal 255 karakter.
- `email`: wajib, format email valid, unik di tabel users.
- `password`: wajib, minimal 8 karakter dan harus cocok dengan `password_confirmation`.

### Login

Request:

```json
{
  "email": "budi@example.com",
  "password": "password123"
}
```

Response berhasil (`200 OK`):

```json
{
  "message": "Login Berhasil",
  "user": {
    "id": 1,
    "name": "Budi",
    "email": "budi@example.com"
  },
  "token": "<personal-access-token>"
}
```

Simpan token dengan aman. Gunakan token tersebut pada header `Authorization: Bearer <token>` untuk mengakses endpoint yang membutuhkan autentikasi.

Jika email atau password salah, API mengembalikan error validasi (`422 Unprocessable Entity`).

### Logout

Request:

`POST /auth/logout`

Header:

`Authorization: Bearer <token>`

Response berhasil (`200 OK`):

```json
{
  "message": "Logout Berhasil"
}
```

Logout mencabut token aktif yang digunakan pada request tersebut.
