# WP Event Calendar Plugin

A simple WordPress plugin that provides:
- Custom Post Type for Events (Photo, Title, Description, Date)
- Shortcodes `[event_list]`  
- REST API endpoints for Events
- Calendar view with frontend JS/CSS

## Installation
1. Upload to `wp-content/plugins/wp-event-calendar`
2. Activate via WP Admin → Plugins
3. Use `[event_list]`  shortcodes in any page/post.

## REST API
- `/wp-json/el/v1/events` → All events 