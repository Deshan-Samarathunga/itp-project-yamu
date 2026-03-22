Yamu database SQL files.

Main files:

- `yamu.sql`: current consolidated schema and seed dump
- `shared_auth_roles.sql`: users, roles, user_roles, password resets
- `customer_module.sql`: customer profile schema
- `driver_module.sql`: driver profile and driver ads schema
- `staff_module.sql`: staff profile, brands, and vehicle listing schema
- `admin_module.sql`: admin tables and admin profile schema
- `commerce_module.sql`: bookings, payments, reviews, complaints, promotions

Migration files should go in `db/migrations/`.
