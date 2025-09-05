# Release Process

This document outlines the release process for the Magpie PHP SDK.

## Prerequisites

- ✅ All tests passing
- ✅ Code reviewed and approved
- ✅ CHANGELOG.md updated
- ✅ Documentation updated
- ✅ Version bumped in relevant files

## Automated Release Process

### 1. Push Changes

```bash
git add .
git commit -m "feat: new feature or fix: bug fix"
git push origin main
```

### 2. Create and Push Git Tag

```bash
# Create a new tag
git tag -a v1.0.1 -m "Release v1.0.1: Description of changes"

# Push the tag
git push origin v1.0.1
```

### 3. Create GitHub Release

- Go to <https://github.com/magpieimdev/magpie-php/releases/new>
- Select your tag (v1.0.1)
- Add a meaningful title: "v1.0.1 - Brief description"
- Add release notes describing changes, fixes, and new features
- Click "Publish release"

### 4. Packagist Auto-Update

Once the GitHub webhook is configured (✅ Done), Packagist will automatically:

- Detect the new release
- Pull the latest code
- Update the package information
- Make the new version available via `composer require`

## Manual Release (Fallback)

If the webhook isn't working, you can manually update Packagist:

1. Go to <https://packagist.org/packages/magpieim/magpie-php>
2. Click "Update" button
3. Wait for the update to complete

## Release Checklist

- [ ] Run full test suite: `composer test`
- [ ] Update CHANGELOG.md with new version and changes
- [ ] Bump version numbers if needed
- [ ] Commit all changes
- [ ] Create and push git tag
- [ ] Create GitHub release
- [ ] Verify Packagist updated automatically
- [ ] Test installation: `composer require magpieim/magpie-php:^1.0.1`
- [ ] Update documentation if needed

## Version Numbering

Follow [Semantic Versioning](https://semver.org/):

- **MAJOR** (1.0.0): Breaking changes
- **MINOR** (1.1.0): New features, backward compatible
- **PATCH** (1.0.1): Bug fixes, backward compatible

## GitHub Actions (Future Enhancement)

Consider setting up GitHub Actions to automate:

- Running tests on tag creation
- Creating GitHub releases automatically
- Notifying team members
- Publishing to multiple package repositories

## Emergency Hotfix Process

For critical bugs in production:

1. Create hotfix branch from main:

   ```bash
   git checkout -b hotfix/critical-bug-fix
   ```

2. Make minimal changes to fix the issue

3. Test thoroughly

4. Create PR and get review

5. Merge and immediately release:

   ```bash
   git tag -a v1.0.2 -m "Hotfix v1.0.2: Critical bug fix"
   git push origin v1.0.2
   ```

6. Create GitHub release with clear notes about the hotfix

## Support

For questions about the release process, contact:

- Jerick Coneras ([@donjerick](https://github.com/donjerick))
- Magpie Team ([support@magpie.im](mailto:support@magpie.im))
