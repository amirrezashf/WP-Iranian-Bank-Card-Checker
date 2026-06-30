# WP Iranian Bank Card Checker

A lightweight WordPress dashboard widget for validating Iranian bank card numbers, checking checksum validity, and detecting the issuing bank based on card prefixes.

## Description

WP Iranian Bank Card Checker adds a minimal and secure dashboard widget to the WordPress admin area. It allows permitted users to validate Iranian bank card numbers directly from the dashboard without using external APIs or third-party services.

The plugin checks the entered card number structure, validates the checksum using the Luhn algorithm, detects the issuing bank from the first six digits, and displays clear validation feedback.

It is useful for WooCommerce stores, support teams, finance teams, order review workflows, and admin-side operational checks where quick card number validation is needed.

## Features

- Adds a lightweight dashboard widget to WordPress
- Validates Iranian bank card number format
- Checks 16-digit card number structure
- Verifies checksum using the Luhn algorithm
- Detects issuing bank from the first six digits
- Supports Persian, Arabic, and English digit normalization
- Displays detailed validation status
- Shows format status, checksum status, bank name, and card prefix
- Minimal admin-side UI
- No external dependencies
- No external API calls
- No database storage
- No card data logging
- Capability-based access control
- Translation-ready strings
- Filterable access capability
- Filterable bank prefix list
- Single-file plugin architecture

## Requirements

- PHP 7.4 or higher
- WordPress 6.0 or higher
- WooCommerce capability support for the default access rule

By default, the dashboard widget is available to users with the `manage_woocommerce` capability.

## Installation

1. Download the plugin file.
2. Create a folder named `wp-iranian-bank-card-checker`.
3. Place the plugin file inside the folder.
4. Make sure the main plugin file is named:

```text
wp-iranian-bank-card-checker.php
```

5. Upload the folder to:

```text
wp-content/plugins/
```

6. Activate the plugin from the WordPress admin panel.
7. Go to the WordPress Dashboard.
8. Use the “Iranian Bank Card Checker” widget.

## Usage / How it Works

After activation, the plugin adds a dashboard widget to the WordPress admin area.

To validate a card number:

1. Open the WordPress Dashboard.
2. Enter a 16-digit Iranian bank card number.
3. Click the check button.
4. Review the validation result.

The widget displays:

- Card number format status
- Checksum status
- Detected bank name
- Detected card prefix
- Validation errors, if any

The plugin performs all checks inside the browser after the dashboard page is loaded. It does not submit entered card numbers to the server.

## Data Storage

This plugin does not store any card data.

It does not create:

- Custom database tables
- WordPress options
- Post meta
- User meta
- Transients
- Logs
- Analytics records

The entered card number is processed only inside the dashboard widget interface and is not saved.

## Development

The plugin is built as a lightweight single-file WordPress plugin and follows WordPress development best practices.

Development principles include:

- WordPress Coding Standards
- Native WordPress APIs
- Secure capability validation
- Escaped outputs
- Translation-ready strings
- Lightweight dashboard integration
- No unnecessary database queries
- No external requests
- No third-party dependencies
- Extensible codebase
- Inline admin CSS registered through WordPress APIs
- Inline admin JavaScript registered through WordPress APIs
- Capability-based access control
- Filterable configuration

The plugin uses:

- Dashboard Widgets API
- Admin enqueue APIs
- Inline CSS
- Inline JavaScript
- WordPress filters
- Browser-side card validation
- Luhn checksum validation

## Hooks

### `wp_ibcc_required_capability`

Filters the required capability for displaying the dashboard widget.

Default value:

```php
manage_woocommerce
```

Example:

```php
add_filter(
	'wp_ibcc_required_capability',
	static function () {
		return 'manage_options';
	}
);
```

### `wp_ibcc_bank_prefixes`

Filters the Iranian bank card prefix list.

Example:

```php
add_filter(
	'wp_ibcc_bank_prefixes',
	static function ( $prefixes ) {
		$prefixes['123456'] = 'Custom Bank Name';

		return $prefixes;
	}
);
```

## Filters

### `wp_ibcc_required_capability`

Allows developers to change the required capability for accessing the dashboard widget.

Default capability:

```php
manage_woocommerce
```

Example:

```php
add_filter(
	'wp_ibcc_required_capability',
	static function () {
		return 'manage_options';
	}
);
```

### `wp_ibcc_bank_prefixes`

Allows developers to modify, remove, or add Iranian bank card prefixes.

Example:

```php
add_filter(
	'wp_ibcc_bank_prefixes',
	static function ( $prefixes ) {
		$prefixes['123456'] = 'Custom Bank Name';

		return $prefixes;
	}
);
```

## Future Improvements

- Admin settings page
- Custom capability selector
- Searchable bank prefix management
- CSV import for bank prefixes
- CSV export for prefix list
- REST API endpoint for validation
- AJAX-based server-side validation option
- Multisite support
- Dashboard widget visibility settings
- Bank logo support
- Validation history with optional privacy controls
- Role-based widget display rules
- WooCommerce order screen integration
- WooCommerce checkout helper integration
- Bulk card prefix checker
- Scheduled prefix list updates
- Admin color theme customization

## License

GPL-2.0-or-later

This plugin is licensed under the GNU General Public License v2.0 or later.

## Author

Amirreza Shayesteh Far

GitHub: https://github.com/amirrezashf

---

# بررسی شماره کارت بانکی ایران برای وردپرس

یک ویجت سبک برای داشبورد وردپرس که شماره کارت‌های بانکی ایران را از نظر ساختار، رقم کنترل و پیش‌شماره بانک بررسی می‌کند.

## توضیحات

افزونه WP Iranian Bank Card Checker یک ویجت ساده، سبک و امن به داشبورد مدیریت وردپرس اضافه می‌کند. این ویجت به کاربران مجاز اجازه می‌دهد شماره کارت‌های بانکی ایران را بدون نیاز به سرویس خارجی یا API شخص ثالث بررسی کنند.

این افزونه ساختار شماره کارت، تعداد ارقام، رقم کنترل بر اساس الگوریتم Luhn و بانک صادرکننده را بر اساس شش رقم اول کارت بررسی می‌کند و نتیجه را به‌صورت واضح در داشبورد نمایش می‌دهد.

این افزونه برای فروشگاه‌های ووکامرس، تیم‌های پشتیبانی، تیم‌های مالی، بررسی سفارش‌ها و عملیات داخلی سایت‌های وردپرسی کاربرد دارد.

## ویژگی‌ها

- افزودن ویجت سبک به داشبورد وردپرس
- بررسی فرمت شماره کارت بانکی ایران
- بررسی ساختار ۱۶ رقمی شماره کارت
- اعتبارسنجی رقم کنترل با الگوریتم Luhn
- تشخیص بانک صادرکننده بر اساس شش رقم اول
- پشتیبانی از تبدیل اعداد فارسی، عربی و انگلیسی
- نمایش وضعیت کامل اعتبارسنجی
- نمایش وضعیت فرمت، رقم کنترل، نام بانک و پیش‌شماره
- رابط کاربری مینیمال در پنل مدیریت
- بدون وابستگی خارجی
- بدون درخواست API خارجی
- بدون ذخیره‌سازی اطلاعات
- بدون ثبت لاگ شماره کارت
- کنترل دسترسی بر اساس capability
- آماده برای ترجمه
- قابلیت تغییر سطح دسترسی با filter
- قابلیت تغییر لیست پیش‌شماره بانک‌ها با filter
- معماری تک‌فایلی

## نیازمندی‌ها

- PHP نسخه 7.4 یا بالاتر
- WordPress نسخه 6.0 یا بالاتر
- پشتیبانی از capability ووکامرس برای سطح دسترسی پیش‌فرض

به‌صورت پیش‌فرض، ویجت فقط برای کاربرانی نمایش داده می‌شود که capability زیر را داشته باشند:

```php
manage_woocommerce
```

## نصب

1. فایل افزونه را دانلود کنید.
2. یک پوشه با نام `wp-iranian-bank-card-checker` بسازید.
3. فایل افزونه را داخل این پوشه قرار دهید.
4. مطمئن شوید نام فایل اصلی افزونه به این شکل باشد:

```text
wp-iranian-bank-card-checker.php
```

5. پوشه افزونه را در مسیر زیر آپلود کنید:

```text
wp-content/plugins/
```

6. افزونه را از پنل مدیریت وردپرس فعال کنید.
7. وارد داشبورد وردپرس شوید.
8. از ویجت بررسی شماره کارت بانکی استفاده کنید.

## نحوه استفاده / عملکرد افزونه

بعد از فعال‌سازی، افزونه یک ویجت به داشبورد مدیریت وردپرس اضافه می‌کند.

برای بررسی شماره کارت:

1. وارد داشبورد وردپرس شوید.
2. شماره کارت ۱۶ رقمی بانکی ایران را وارد کنید.
3. روی دکمه بررسی کلیک کنید.
4. نتیجه اعتبارسنجی را مشاهده کنید.

ویجت موارد زیر را نمایش می‌دهد:

- وضعیت فرمت شماره کارت
- وضعیت رقم کنترل
- نام بانک تشخیص داده‌شده
- پیش‌شماره کارت
- خطاهای اعتبارسنجی، در صورت وجود

بررسی‌ها بعد از بارگذاری صفحه داشبورد، داخل مرورگر انجام می‌شوند و شماره کارت واردشده به سرور ارسال نمی‌شود.

## ذخیره‌سازی داده

این افزونه هیچ اطلاعاتی از شماره کارت ذخیره نمی‌کند.

افزونه موارد زیر را ایجاد نمی‌کند:

- جدول اختصاصی دیتابیس
- option در وردپرس
- post meta
- user meta
- transient
- log
- رکوردهای analytics

شماره کارت واردشده فقط داخل رابط ویجت داشبورد پردازش می‌شود و ذخیره نمی‌شود.

## توسعه

این افزونه با معماری سبک و تک‌فایلی توسعه داده شده و از اصول استاندارد توسعه افزونه وردپرس پیروی می‌کند.

اصول توسعه شامل موارد زیر است:

- WordPress Coding Standards
- استفاده از Native WordPress APIs
- اعتبارسنجی امن capability
- خروجی‌های escape شده
- متن‌های آماده ترجمه
- اتصال سبک به داشبورد وردپرس
- بدون query غیرضروری دیتابیس
- بدون درخواست خارجی
- بدون وابستگی شخص ثالث
- ساختار قابل توسعه
- ثبت CSS داخلی از طریق API وردپرس
- ثبت JavaScript داخلی از طریق API وردپرس
- کنترل دسترسی بر اساس capability
- تنظیمات قابل تغییر با filter

این افزونه از موارد زیر استفاده می‌کند:

- Dashboard Widgets API
- Admin enqueue APIs
- Inline CSS
- Inline JavaScript
- WordPress filters
- اعتبارسنجی سمت مرورگر
- اعتبارسنجی Luhn checksum

## هوک‌ها

### `wp_ibcc_required_capability`

این filter سطح دسترسی لازم برای نمایش ویجت داشبورد را تغییر می‌دهد.

مقدار پیش‌فرض:

```php
manage_woocommerce
```

نمونه استفاده:

```php
add_filter(
	'wp_ibcc_required_capability',
	static function () {
		return 'manage_options';
	}
);
```

### `wp_ibcc_bank_prefixes`

این filter لیست پیش‌شماره‌های کارت بانکی ایران را تغییر می‌دهد.

نمونه استفاده:

```php
add_filter(
	'wp_ibcc_bank_prefixes',
	static function ( $prefixes ) {
		$prefixes['123456'] = 'نام بانک سفارشی';

		return $prefixes;
	}
);
```

## فیلترها

### `wp_ibcc_required_capability`

به توسعه‌دهندگان اجازه می‌دهد capability لازم برای مشاهده ویجت داشبورد را تغییر دهند.

capability پیش‌فرض:

```php
manage_woocommerce
```

نمونه استفاده:

```php
add_filter(
	'wp_ibcc_required_capability',
	static function () {
		return 'manage_options';
	}
);
```

### `wp_ibcc_bank_prefixes`

به توسعه‌دهندگان اجازه می‌دهد پیش‌شماره‌های کارت بانکی ایران را تغییر دهند، حذف کنند یا مورد جدیدی اضافه کنند.

نمونه استفاده:

```php
add_filter(
	'wp_ibcc_bank_prefixes',
	static function ( $prefixes ) {
		$prefixes['123456'] = 'نام بانک سفارشی';

		return $prefixes;
	}
);
```

## بهبودهای آینده

- صفحه تنظیمات در پنل مدیریت
- انتخاب capability سفارشی
- مدیریت قابل جستجوی پیش‌شماره بانک‌ها
- import پیش‌شماره‌ها از CSV
- export لیست پیش‌شماره‌ها به CSV
- endpoint برای REST API
- اعتبارسنجی سمت سرور با AJAX
- پشتیبانی از Multisite
- تنظیمات نمایش ویجت داشبورد
- پشتیبانی از لوگوی بانک‌ها
- تاریخچه اعتبارسنجی با کنترل‌های حریم خصوصی اختیاری
- قوانین نمایش ویجت بر اساس نقش کاربری
- اتصال به صفحه سفارش‌های WooCommerce
- کمک‌ابزار در checkout ووکامرس
- بررسی گروهی پیش‌شماره کارت‌ها
- به‌روزرسانی زمان‌بندی‌شده لیست پیش‌شماره‌ها
- شخصی‌سازی رنگ رابط کاربری مدیریت

## مجوز

GPL-2.0-or-later

این افزونه تحت مجوز GNU General Public License v2.0 or later منتشر می‌شود.

## نویسنده

Amirreza Shayesteh Far

GitHub: https://github.com/amirrezashf
