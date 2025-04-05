# WP Petition

WP Petition is a WordPress plugin for petition campaigns where users can sign and support your cause. Users can express interest in campaigns or products and provide feedback.

## Description

WP Petition allows you to create petition campaigns where supporters can sign and express their interest. This is perfect for community projects, non-profits, or any initiative that needs to gauge public interest and support.

### Key Features

- **Petition Signatures**: Collect signatures and support from users
- **Interest Tracking**: Users can indicate if they would use your product/service
- **Campaign Management**: Create and manage multiple campaigns
- **Progress Tracking**: Display progress towards signature goals
- **Voter Lists**: Show all signatories in a scrollable table
- **Shortcodes**: Easy integration with any WordPress page or post
- **Export Functionality**: Export voter data
- **Responsive Design**: Works on all devices

## Installation

1. Upload the `wp-petition` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Petition' in your admin menu to create your first campaign

## Usage

### Creating a Campaign

1. Go to Petition > Add Campaign in your WordPress admin
2. Fill in the campaign details:
   - Title
   - Description
   - Votes Goal
   - Start/End Dates (optional)
   - Associated Page (optional)
3. Click "Create Campaign"

### Adding Campaign Elements to Pages

Use these shortcodes to display campaign elements on your pages:

- `[petition_vote_form id=X lang=en|de|es]` - Displays the petition form for campaign X in the specified language (English, German, or Spanish)
- `[petition_vote_list id=X]` - Displays the list of signatories for campaign X
- `[petition_votes_count id=X display=bar]` - Displays the votes progress bar
- `[petition_votes_count id=X display=text]` - Displays the votes count as text

### Petition Form

The petition form supports multiple languages (English, German, Spanish) and includes:
- Name field (required)
- Email field (required)
- Checkbox for "Would you use this in your life or community?"
- Dropdown list for contribution role (Ideas, Energy, Writing, Tech, Organizing)
- Text field for "Feature Requests or Thoughts"
- Submit button

Admins receive an email notification upon submission.

### Managing Petitions

1. Go to Petition > Campaigns in your WordPress admin
2. View campaign statistics and voter information
3. Export voter lists

## Customization

You can customize the plugin's appearance by overriding the CSS styles in your theme.

## Creating a ZIP File

To create a distributable ZIP file of the plugin:

1. Navigate to the plugin directory
2. Run the included script: `./build.sh`
3. The script will create a ZIP file named `wp-petition-1.0.8.zip` (or with your current version number)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Your Name
