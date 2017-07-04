# Auto Login IP

## UNMAINTAINED PROJECT
This project is no longer maintained. Any issues will not be actively picked up. Pull Requests will be merged unchecked. The sources here are up-for-grabs.

## Introduction
When you have a site that is only accessible for registered users, it might be a pain to login every time - even when your login credentials are filled in automatically. This plugin allows you to configure a specific user ID and an IP address (or range of IP addresses). You are logged in automatically as that user once you visit the site from that IP.
Get started now

## Dealing with offline-pages properly
When developing a Joomla! frontend-site, you probably take the site **Offline** by modifying the **Global Configuration**. But it takes more time to finish, you don't want to have your early adapters see an ugly Joomla! offline-message showing a login-box: That gives the wrong message - you do not want them to login at all. So beautifying the offline-page is a good step forward: Put up a nice picture, some text telling everybody to come back in a year or so.

But having no login-box also gives you the problem of not being able to login so easily. This plugin solves that. Just enter your IP-address in the plugin-options, enter the user-ID of the person you want to login as, and you're done: Once you visit your Joomla! offline frontend, you will be logged in automatically. It also works excellent while testing through portable devices (mobiles, tablets). No need to type in difficult usernames and passwords.

## IP matching
Authentication is configured with either a simple **IP** field and **Userid** field in the **Basic Parameters** or a more advanced **Userid-IP** mapping under the **Advanced Parameters**. In both cases, the **Userid** is simply the ID that you copy from the Joomla! User Manager, while the **IP** match can be done using a couple of matching rules:

- **Direct match**: The value of the IP matches the IP-address directly. For instance: `127.0.0.1`
- **IP with wildcard matching**: The value contains star-wildcards `*` to match various numbers at once. For instance: `127.*.0.*`
- **IP ranges**: The value contains a range-start and range-end, seperated by a dash -. For instance: `127.0.0.0-127.0.0.2`.
- **Multiple matches**: Multiple matches for a single user can be separated by commas.

The **Userid-IP** mapping contains a list of newline-seperated mappings that map an userid (on the left) with an IP (on  the right). For instance, the following rules would login anybody on the network `192.168` as Joomla! user with ID `42`, while anybody on the network `192.169` would be logged in as Joomla! user with ID `43`.

  42=192.168.*.*
  43=192.168.0.0-192.168.0.255

## Security warning
Ofcourse be aware that there is a security risk. If a hacker decides to spoof your IP-address, he's in. Note that the hacker will still need to guess that AutoLoginIp is actually, and needs to find out your IP. When the hacker is in, he's only logged into the Joomla! frontend (the plugin doesn't do anything in the Joomla! backend). If you're a bit scared of this to happen (whatever), just configure a Joomla! user with limited rights (so, a Registered user, not a Super User).
