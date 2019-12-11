# Matomo LoginFilterIp Plugin

## Description

This plugin makes possible to filter (whitelist) the ip/ranges allowed to
login in your Matomo instance with username/password credentials.

This is different from the [whitelisting
feature](https://matomo.org/faq/how-to/faq_25543/) included in Matomo because
the filter is only applied to the login action when using username and password
and not when providing a single sign-on experience using the [logme
action](https://matomo.org/faq/how-to/faq_30/) provided by the Login plugin.

In this scenario is possibile to completely hide the login form and return an
error page or redirect the user to another address.

## Installation

Refer to [this Matamo FAQ](https://matomo.org/faq/plugins/faq_21/).

## Usage

Add the following section to your `config.ini.php`:

```
[LoginFilterIp]
allow_login_from[] = <IP ADDRESS or RANGE>
; uncomment to redirect the user instead of displaying an error page
;redirect_unallowed_to = <URL>
```

**Make sure you have direct access to the `config.ini.php` file before using this
plugin as it could prevent you from authenticating in your Matomo instance.**
