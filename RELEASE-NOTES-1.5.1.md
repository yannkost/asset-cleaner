# Release Notes — 1.5.1

This patch release fixes database scan storage setup for fresh installations and upgrades.

## Fixes

- Fresh plugin installations now create all scan storage tables, regardless of the selected storage mode.
- An upgrade migration creates missing scan storage tables while preserving existing tables and scan data, repairing affected installations when Craft applies the release's migrations.

## Updating

Apply Craft's pending migrations after updating the plugin. The plugin schema version is now `1.1.4` so Craft detects the upgrade migration.
