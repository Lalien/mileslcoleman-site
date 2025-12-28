# Google Analytics Setup Guide

This site is now configured to use Google Analytics 4 (GA4) with proper cookie consent integration.

## Setup Instructions

### 1. Create a Google Analytics 4 Property

1. Go to [Google Analytics](https://analytics.google.com/)
2. Click "Admin" in the bottom left
3. Click "Create Property"
4. Follow the setup wizard to create a new GA4 property
5. You'll receive a Measurement ID (format: `G-XXXXXXXXXX`)

### 2. Update the Measurement ID

Replace the placeholder `G-XXXXXXXXXX` with your actual Measurement ID in the following file:

**File:** `index.html` (lines 17 and 28)

```html
<!-- Change this line -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-XXXXXXXXXX"></script>

<!-- And this line -->
gtag('config', 'G-XXXXXXXXXX');
```

### 3. Rebuild the Site

After updating the Measurement ID:

```bash
npm run build
```

### 4. Deploy

Deploy the updated `dist` folder to your hosting provider.

## Features

### Cookie Consent Integration

The Google Analytics implementation is fully integrated with the cookie consent modal:

- **Consent Required**: Analytics tracking is disabled by default until the user accepts cookies
- **Accept Cookies**: When users click "Accept", analytics tracking is enabled
- **Decline Cookies**: When users click "Decline", analytics tracking remains disabled
- **Persistent Choice**: User consent choice is saved for 365 days

### Privacy Compliance

This implementation follows privacy best practices:

- Uses Google's Consent Mode API
- Sets `analytics_storage` to `denied` by default
- Only grants consent after explicit user acceptance
- Respects user privacy choices across sessions

## Verification

To verify Google Analytics is working:

1. Visit your site in a browser
2. Accept the cookie consent
3. Open Google Analytics dashboard
4. Go to Realtime > Overview
5. You should see your active session

## Troubleshooting

### No Data Showing Up

- Verify you replaced `G-XXXXXXXXXX` with your actual Measurement ID
- Check browser console for any JavaScript errors
- Ensure you accepted the cookie consent modal
- Wait a few minutes for data to appear in Google Analytics

### Data Showing Before Consent

If you see tracking before users accept cookies, verify:
- The default consent is set to 'denied' in `index.html`
- The `updateGAConsent()` function is properly called in `src/index.ts`

## Advanced Configuration

### Custom Events

To track custom events, use the `gtag()` function:

```javascript
if (typeof window.gtag === 'function') {
  window.gtag('event', 'button_click', {
    'event_category': 'engagement',
    'event_label': 'hero_cta'
  });
}
```

### Additional Configuration

You can add more gtag configuration options:

```javascript
gtag('config', 'G-XXXXXXXXXX', {
  'page_title': 'Custom Page Title',
  'page_path': '/custom-path'
});
```

## Support

For more information, visit:
- [Google Analytics 4 Documentation](https://support.google.com/analytics/answer/10089681)
- [Consent Mode Documentation](https://support.google.com/analytics/answer/9976101)
