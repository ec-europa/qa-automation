# Cron checks
Your code will be scanned for cron hooks so the QA team can request a cronjob for
your project if necessary. If you have a cron hook defined in one of your features
or custom modules please provide the cron requirements inside of the doc block of the
function. If you decide on using poormanscron this is not necessary.

```php
/**
 * Implements hook_cron().
 *
 * MULTISITE-XXXXX: cronjob for <projectname> running at 5 * * * * (every hour on
 * on five minutes past the hour).
 */
function mymodule_cron() {
  variable_set('mymodule_cron', 'success');
}
```

This way the QA team can verify your cron is running at the requested intervals.
The command for checking this is:
```
crontab -l | grep <projectname>
```