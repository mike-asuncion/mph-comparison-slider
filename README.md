# MPH Comparison Slider

A WordPress plugin that adds a draggable **design-vs-HTML comparison slider** to portfolio projects. Showcase your design source (Figma, Sketch, Adobe XD, etc.) next to the live HTML build — drag the handle to reveal one over the other.

Built for the [MikeAsuncion portfolio theme](https://mikeasuncion.ph), but works as a standalone plugin on any WordPress site that has a `portfolio_project` custom post type.

## Features

- **Comparison Screenshots meta box** on the `portfolio_project` CPT — pick a design screenshot and an HTML screenshot from the media library.
- **Customizable design-tool label** so the slider identifies as Figma, Sketch, Adobe XD, or whatever you use.
- **Browser-chrome decoration** on the HTML side (traffic-light dots + URL pill that reads the `project_url` post meta).
- **Design-tool toolbar decoration** on the design side.
- **Vanilla-JS interactions**: pointer drag, touch, and full keyboard support (Arrow keys / Home / End).
- **Edge-aware side labels** that fade out when their side is no longer visible.
- **Click-to-navigate**: the thumbnail variant doubles as a link to the project's single page; dragging never triggers navigation.
- **Two variants** — `thumb` (small, clickable) and `hero` (full-width).

## Installation

1. Copy the `mph-comparison-slider` folder to `wp-content/plugins/`.
2. Activate **MPH Comparison Slider** in WordPress admin → Plugins.
3. Edit any project, fill the new "Comparison Screenshots" meta box, and save.

## Usage

### PHP

```php
if ( function_exists( 'mph_has_comparison_screenshots' )
    && mph_has_comparison_screenshots( get_the_ID() ) ) {

    echo mph_render_comparison_slider( get_the_ID(), array(
        'variant' => 'hero', // 'thumb' | 'hero'
        'link'    => false,  // navigate to permalink on tap (thumb only)
    ) );
}
```

### Shortcode

```
[mph_comparison_slider id="123" variant="thumb"]
```

Attributes:
- `id` — post ID (required)
- `variant` — `thumb` (default) or `hero`
- `link` — `yes` (default) or `no`

## Requirements

- WordPress 5.0+
- PHP 7.4+
- A custom post type named `portfolio_project` (or change `MPH_CS_POST_TYPE` in the main plugin file)

## Credits

Built by **Mike Asuncion** ([@mike-asuncion](https://github.com/mike-asuncion)), co-developed with **[Claude Code](https://claude.com/claude-code)** (Anthropic Claude Opus 4.7) — plugin architecture, slider interaction, admin meta box, and styling.

## License

GPL-2.0-or-later (matches the WordPress core license).
