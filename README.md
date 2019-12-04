# wp-profiles

Allow your users to sign-up to your site easily

## Requirements

 - WordPress 5.x
 - PHP 5.3+

## Installation

Download and unzip into your `wp-content/plugins` folder.

Make sure the folder name is `wp-profiles`.

_Note: The `samples` folder is not required, it just contains sample templates and code for handling the actions (see below)._

In your WordPress go to the **Dashboard** and then to **Plugins**, find the **Profiles** plugin and activate it.

## Usage

- Create your four templates or use the sample ones.
- Create your four pages: Register, Sign-in, Recover and Profile. Assign the appropiate templates.
- Go to the **Dashboard** and then to **Profiles**, select the corresponding pages.
- In your `functions.php` file register the actions for sending the mail messages (for activation and recovering).
- Profit!

### Customizing templates

You may customize the four pages to your liking (or to match your HTML/CSS/JS framework), to do so just take the four included templates and modify them. Just make sure you don't change the PHP code that allows them to work.

### Customizing mailings

You may also customize the mailings sent by the plugin.

To do so just **copy** the **contents** of the sample `functions.php` file into the one inside your theme folder. **Don't overwrite it or you will break your site** - Just append the contents of the sample one.

The sample functions use the `wp_mail` function, but you may use any mechanism to send the mail messages (for example PHPMailer).

Just place your code inside both actions and use the contents of the `$params` argument to craft and send your messages. The `$params` variable contains:

- `from` - `array` with the source address and name, derived from your WordPress site options. Takes the form `['no-reply@yoursite.com' => 'Your Site']`
- `to` - `array` with the destination address and name. Takes the form `['john.doe@mailinator.com' => 'John Doe']`
- `subject` - The subject of the message. Takes the form `Recover your account | Your Site` or `Activate your account | Your Site`
- `link` - The activation/recovery link. Takes the form `http://yoursite.com/[register-slug]?[auth-parameter]=[auth-hash]`

You can override the default values if you need to.

### Get permalinks

The plugin includes a function to retrieve the permalink to any of the related pages.

To use it call `Profiles::getPermalink($slug)` where `$slug` can be one of:

- `register`
- `login`
- `profile`
- `recover`

### Translating the messages

The plugin includes a `.po` file that you may translate into another language to localize the UI and messages.

If you create a translation please send me a pull request so that I include it in the repo.

## License

Released under the **MIT License**.

## Author

biohzrdmx - https://github.com/biohzrdmx - https://biohzrdmx.me