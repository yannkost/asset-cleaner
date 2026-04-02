# Shared Scan Workspace Setup for Multi-Container Environments

This guide explains how to configure **Asset Cleaner** when your Craft web requests and queue runner do **not** run in the same container.

## Why this setup is needed

Asset Cleaner stores scan progress and temporary scan data in a file-backed workspace.

By default, that workspace lives under:

`@storage/asset-cleaner/scans/<scanId>/`

If your **web container** starts the scan, but your **queue container** processes the queue job, both containers must be able to read and write the **same shared scan workspace path**.

If they do not share the same filesystem path, the queue worker may fail with errors like:

- `Scan metadata not found for 'scan_...'`
- missing `meta.json`
- scan starts in the control panel, but fails immediately in the queue

---

## Recommended solution

If you want to keep using **file-based scan storage**, configure a **shared mounted directory** that is available in **both** the web container and the queue container, and point Asset Cleaner at that directory.

If your infrastructure does **not** provide a shared filesystem between web and queue workers, use **database-based scan storage** instead. Database mode is better suited for containerized or cloud-style environments where shared filesystem access is not guaranteed.

## Configuration options

Asset Cleaner supports two scan storage modes:

1. **File-based** - stores scan state in a shared filesystem workspace
2. **Database-based** - stores scan state in the database

For file-based mode, Asset Cleaner supports a custom scan workspace path via either:

1. the `ASSET_CLEANER_SCAN_PATH` environment variable
2. the `scanWorkspacePath` setting in `config/asset-cleaner.php`

The recommended setup for file-based mode is to use **both**:

- environment variable for the actual path
- config file to read that environment variable

---

## Step 1: Create a shared mount

Choose a path that is mounted into **both** containers.

Example shared path inside both containers:

`/shared/asset-cleaner-scans`

This directory must:

- exist or be creatable
- be writable by both the web process and the queue process
- resolve to the **same underlying shared volume** in both containers

---

## Step 2: Set the environment variable

Set this environment variable in **both** the web container and the queue container:

`ASSET_CLEANER_SCAN_PATH=/shared/asset-cleaner-scans`

The value must be identical in both containers.

---

## Step 3: Add the plugin config file

Create this file in your Craft project:

`config/asset-cleaner.php`

with the following contents:

```php
<?php

return [
    'scanWorkspacePath' => '$ASSET_CLEANER_SCAN_PATH',
];
```

This tells the plugin to use the shared path from the environment variable.

---

## Step 4: Verify permissions

Make sure both containers can write to the shared path.

The directory must allow the application user in both containers to:

- create directories
- create files
- rename files
- read existing files

If permissions are wrong, scans may still fail even if the path is shared.

---

## Step 5: Retry a scan

After configuration:

1. clear/redeploy config if needed
2. restart or reload the web and queue containers if required by your setup
3. start a new scan from the Asset Cleaner utility
4. confirm that the queue worker can continue past the setup stage

---

# Example Docker-style setup

The exact syntax depends on your infrastructure, but conceptually both services must mount the same volume.

## Example concept

- web container mount:
  - shared volume -> `/shared/asset-cleaner-scans`
- queue container mount:
  - same shared volume -> `/shared/asset-cleaner-scans`

and both services must have:

`ASSET_CLEANER_SCAN_PATH=/shared/asset-cleaner-scans`

---

# What the plugin stores there

Asset Cleaner writes transient scan files such as:

- `meta.json`
- `progress.json`
- `state.json`
- asset chunk files
- used-ID files
- final scan result files

These are operational scan files, not public assets.

---

# Troubleshooting checklist

If scans still fail, verify the following:

## 1. Same path in both containers
Confirm both containers use the same value for:

`ASSET_CLEANER_SCAN_PATH`

## 2. Same underlying shared storage
Even if the path string is identical, make sure it points to the same mounted storage in both containers.

## 3. Directory is writable
Test that both web and queue processes can create and read files in the shared directory.

## 4. Queue is using the updated environment/config
If the queue worker was started before the environment variable or config file was added, restart it.

## 5. New scan only
Old failed scans will not recover automatically. Start a **new** scan after configuration is fixed.

---

# Expected behavior after correct setup

Once configured correctly:

- the web request creates the scan workspace
- the queue worker can read the same scan metadata
- progress updates continue normally
- the scan completes without the missing metadata error

---

# Notes

- This setup is specifically important for **multi-container** or **multi-node** deployments.
- On a single-server installation where web and queue share the same local storage, the default configuration is usually sufficient.
- If your infrastructure cannot provide shared filesystem access between web and queue workers, a database-backed scan state architecture may be required in a future version.

---

# Summary

For multi-container environments, Asset Cleaner requires a **shared scan workspace**.

Use:

- a shared mounted directory
- the same `ASSET_CLEANER_SCAN_PATH` in both containers
- `config/asset-cleaner.php` with:

```php
<?php

return [
    'scanWorkspacePath' => '$ASSET_CLEANER_SCAN_PATH',
];
```

This ensures both the web process and the queue worker operate on the same scan files.