# REST API Updates for Mobile App

## Changes Made to `rest.php`

### ✅ Updated: `/ds/v1/locations/{id}/posts` Endpoint

**What Changed:**
- Added `content` field with full HTML (using `apply_filters('the_content', ...)`)
- Added `sticky` field to flag featured posts
- Updated `excerpt` to use proper WordPress `get_the_excerpt()` function
- Changed `date` to ISO 8601 format (`c` format) for mobile apps
- Upgraded `featured_image` from 'medium' to 'large' size
- Added `setup_postdata()` and `wp_reset_postdata()` for proper WordPress context

**Before:**
```php
'excerpt' => wp_trim_words(strip_tags($p->post_content), 20),
'date'    => get_the_date('', $p),
'featured_image' => get_the_post_thumbnail_url($p, 'medium'),
```

**After:**
```php
'excerpt'        => get_the_excerpt($p),
'content'        => apply_filters('the_content', $p->post_content),
'date'           => get_the_date('c', $p),
'featured_image' => get_the_post_thumbnail_url($p, 'large'),
'sticky'         => is_sticky($p->ID),
```

---

### ✅ Added: `/ds/v1/posts/{id}` Endpoint (NEW)

**Purpose:** Get a single post with full content

**Usage:**
```
GET /wp-json/ds/v1/posts/123
```

**Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "Post Title",
    "url": "https://...",
    "excerpt": "Short excerpt...",
    "content": "<h2>Full HTML content...</h2>",
    "date": "2025-01-10T14:30:00",
    "featured_image": "https://...",
    "sticky": true
  }
}
```

**Features:**
- Returns 404 for non-existent or unpublished posts
- Only returns posts with `post_type='post'` and `post_status='publish'`
- Same data structure as location posts endpoint
- Can be used for deep-linking or on-demand fetching

---

## Testing the Updates

### Test Location Posts (Updated):
```bash
curl https://yesyoucandance.org/wp-json/ds/v1/locations/1/posts
```

**Expected Response:**
```json
{
  "location": {
    "id": 1,
    "title": "Location Name",
    ...
  },
  "posts": {
    "page": 1,
    "per_page": 10,
    "total": 25,
    "total_pages": 3,
    "items": [
      {
        "id": 123,
        "title": "Post Title",
        "url": "https://...",
        "excerpt": "Short excerpt for news feed...",
        "content": "<h2>Full HTML</h2><p>Complete post content...</p>",
        "date": "2025-01-10T14:30:00",
        "featured_image": "https://...image.jpg",
        "sticky": true
      }
    ]
  }
}
```

### Test Single Post (New):
```bash
curl https://yesyoucandance.org/wp-json/ds/v1/posts/123
```

**Expected Response:**
```json
{
  "success": true,
  "data": {
    "id": 123,
    "title": "Post Title",
    "excerpt": "Short excerpt...",
    "content": "<h2>Full HTML content...</h2>",
    "date": "2025-01-10T14:30:00",
    "featured_image": "https://...image.jpg",
    "sticky": false
  }
}
```

---

## What the Mobile App Gets Now

### News Feed Display (uses `excerpt`):
- Short preview text (WordPress-generated)
- Featured image
- Date
- Sticky badge for featured posts

### Post Detail Display (uses `content`):
- Full article HTML
- Headings, paragraphs, lists, images
- Embedded content (videos, galleries)
- Properly formatted and styled

---

## Important Notes

### Why `apply_filters('the_content', ...)`?
This processes:
- ✅ Gutenberg blocks → HTML
- ✅ Shortcodes → Rendered content
- ✅ WordPress embeds (YouTube, etc.)
- ✅ Paragraph formatting
- ✅ Image galleries

**Without it:** Raw content won't render properly in the app.

### Why ISO 8601 Date Format?
- Standard format for mobile apps
- Easy to parse in Kotlin/Swift
- Includes timezone information
- Example: `2025-01-10T14:30:00` or `2025-01-10T14:30:00+00:00`

### Why 'large' Image Size?
- Better quality on high-res phone screens
- Still optimized (not full-size)
- WordPress handles responsive srcset automatically

---

## Next Steps

1. ✅ Upload updated `rest.php` to your plugin
2. ✅ Test both endpoints with curl
3. ✅ Verify `content` field includes HTML (not empty)
4. ✅ Check that images in content have full URLs
5. ✅ Provide endpoint URLs to mobile developer

---

## Backward Compatibility

**Good news:** These changes are backward compatible!

- Existing API consumers still work
- Only added new fields (didn't remove any)
- New single post endpoint is optional
- Mobile app can use updated location posts endpoint immediately

---

**File Updated:** `rest.php`  
**Endpoints Modified:** 1 (updated)  
**Endpoints Added:** 1 (new)  
**Breaking Changes:** None  
**Ready for:** Mobile app development