# Release Notes — 1.3.1

## Summary

This release tightens asset usage detection, reduces logging noise, and makes the usage code easier to maintain.

The main focus of `1.3.1` is correctness and cleanup:
- relation fallback usage is more accurate
- trashed relation sources no longer count as usage
- invalid field-handle warnings are resolved by using real field layout context
- temporary debugging noise has been removed from the dedicated plugin log
- the large asset usage service was split into smaller focused collaborators

## Highlights

### More accurate relation fallback behavior
Asset usage checks now handle fallback relation sources more safely and consistently.

Fixes include:
- draft-only relation sources no longer count when draft usage is disabled
- revision-only relation sources no longer count when revision usage is disabled
- trashed relation sources are excluded from fallback usage checks
- relation source resolution works more reliably across sites and owner states

### Cleaner production logging
The dedicated Asset Cleaner log is now quieter and more useful in production.

This release removes temporary investigation logs that were added while debugging fallback relation resolution, while keeping meaningful warnings in place for real operational issues.

### Field-layout-aware content usage checks
Content usage scanning now reads only HTML-capable fields that actually belong to the current element’s field layout.

This fixes warnings such as:
- `Invalid field handle: redactor`
- `Invalid field handle: richtext`
- `Invalid field handle: testRichText`

It also makes content scanning safer in installations where field availability varies by entry type or field layout context.

### Internal service refactor
`AssetUsageService` has been split into smaller focused services to make future maintenance safer and easier.

The new internal structure separates:
- entry usage resolution
- relation usage resolution
- content usage scanning

The public plugin service API remains unchanged.

## Changed
- Split the large asset usage service into focused collaborators while preserving the existing public API
- Standardized plugin logging and removed temporary fallback-relation investigation traces
- Reduced scan lookup memory usage during content scanning

## Fixed
- Fixed fallback relation usage checks for draft and revision inclusion rules
- Fixed fallback relation handling so trashed relation sources do not count as usage
- Fixed relation source resolution across sites and owner states
- Fixed invalid field-handle warnings by using actual element field layouts during content usage checks
- Hardened content field layout discovery so problematic layouts fail safely with warnings instead of breaking scans

## Upgrade Notes
No special migration steps are required for this release.

## Version
- Previous: `1.3.0`
- Current: `1.3.1`
