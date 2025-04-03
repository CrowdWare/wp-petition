# WP Petition

WP Petition is a WordPress plugin for time-based crowdfunding campaigns where users can donate their time instead of money. Users can promote campaigns or products through social media posts.

## Description

WP Petition allows you to create crowdfunding campaigns where supporters can donate their time rather than money. This is perfect for community projects, non-profits, or any initiative that values time contributions.

### Key Features

- **Time-based Donations**: Collect time commitments from supporters
- **Social Media Integration**: Supporters can pledge to promote on Facebook and X (Twitter)
- **Campaign Management**: Create and manage multiple campaigns
- **Progress Tracking**: Display progress towards time and monetary goals
- **Donor Lists**: Show all time donors in a scrollable table
- **Shortcodes**: Easy integration with any WordPress page or post
- **Export Functionality**: Export donor data as PDF
- **Responsive Design**: Works on all devices

## Installation

1. Upload the `wp-petition` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to 'Petition' in your admin menu to create your first campaign
4. The addon stripe_payments has to be installed manually.
5. In order to activate stripe payments we have to enable this option manually because of a bug in our addon.
'''sql
INSERT INTO fds9_options (option_name, option_value, autoload) VALUES ('wp_petition_stripe_integration', '1', 'yes') 
'''

## Usage

### Creating a Campaign

1. Go to Petition > Add Campaign in your WordPress admin
2. Fill in the campaign details:
   - Title
   - Description
   - Hours Goal
   - Money Goal (optional, for integration with Stripe)
   - Start/End Dates (optional)
   - Associated Page (optional)
3. Click "Create Campaign"

### Adding Campaign Elements to Pages

Use these shortcodes to display campaign elements on your pages:

- `[petition_form id=X]` - Displays the donation form for campaign X
- `[petition_donors id=X]` - Displays the donors list for campaign X
- `[petition_progress id=X type=hours display=bar]` - Displays the hours progress bar
- `[petition_progress id=X type=money display=bar]` - Displays the money progress bar
- `[petition_vote_form id=X]` - Displays the interest collection form for campaign X
- `[petition_vote_list id=X]` - Displays the list of votes for campaign X

### Collecting Interest Before Crowdfunding

You can now collect user interest before launching a crowdfunding campaign. This helps determine whether a project is needed by users before moving to the crowdfunding phase.

Use the shortcode `[petition_vote_form id=X]` to display the interest collection form, which includes:
- Email field (required)
- Checkbox for product interest
- Dropdown list for contribution role (Developer, Marketing, Tester)
- Free text field for additional notes

Admins receive an email notification upon submission.

### Donation Form

The donation form includes:
- Name field (required)
- Email field (required)
- Checkboxes for "Facebook Posts" and "X Post"
- Text field for "Sonstiges" (Other support)
- Numeric field for "Stunden" (Hours) (minimum 1, required)
- "Zeit spenden" (Donate time) button

### Managing Donations

1. Go to Petition > Campaigns in your WordPress admin
2. View campaign statistics and donor information
3. Export donor lists as PDF

## Customization

You can customize the plugin's appearance by overriding the CSS styles in your theme.

## Integration with Minutos

WP Petition supports [Minutos](https://minuto.org/de) as a complementary currency. Minutos are time-based vouchers that can be donated to campaigns.

### Using Minutos

1. Use the shortcode `[petition_form id=X type=minutos]` to display a Minutos donation form
2. Donors can pledge Minutos through the form and then send the physical Minutos by mail
3. Campaign administrators can mark Minutos as received in the admin area
4. The plugin automatically converts Minutos to monetary value (2 Minutos = 1 Euro)

### Displaying Minutos Donations

Use these shortcodes to display Minutos donations:

- `[petition_donors id=X type=minutos]` - Displays only Minutos donors
- `[petition_progress id=X type=minutos display=bar]` - Displays the Minutos progress bar


### Displaying Money Donations

Use these enhanced shortcodes to display monetary donations:

- `[petition_donors id=X type=hours]` - Displays only hours donors
- `[petition_donors id=X type=money]` - Displays only money donors
- `[petition_donors id=X type=minutos]` - Displays only Minutos donors
- `[petition_donors id=X type=both]` - Displays all types of donors
- `[petition_progress id=X type=money display=bar]` - Displays the money progress bar

The plugin will automatically calculate the total amount donated through the associated Stripe products and display it in the progress bar and statistics.

## Creating a ZIP File

To create a distributable ZIP file of the plugin:

1. Navigate to the plugin directory
2. Run the included script: `./create-zip.sh`
3. The script will create a ZIP file named `wp-petition-1.0.0.zip` (or with your current version number)

## Requirements

- WordPress 5.0 or higher
- PHP 7.2 or higher
- MySQL 5.6 or higher

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Your Name
