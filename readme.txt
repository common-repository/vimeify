=== Vimeify ===
Contributors: DarkoG, codeverve
Tags: vimeo, upload vimeo, embed video, video, upload
Stable Tag: 1.0.0-beta1
Requires at least: 4.2
Requires PHP: 7.3
Tested up to: 6.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Upload, manage and display Vimeo videos on your sites, beautifully.

== Description ==

**Integrates your WordPress site with Vimeo.com using the Vimeo API and allows the user to upload videos directly from WordPress. 8-)**

Especially useful in the following cases:

* If you want to speed up the entire process. No need to log in to Vimeo, you can now upload videos to Vimeo directly from WordPress.
* If you don't want to share your Vimeo login credentials with other people especially when running multi-author blog.
* If you want to accept videos in the front-end forms (WPForms or GravityForms) uploaded directly to your Vimeo account.

=== How it works ===

In order to be able to connect to Vimeo you will need to sign up on <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> and request access to the Vimeo API. Please check the Installation tab and also the **Screenshot #5**.

<a href="https://vimeify.com/documentation/" target="_blank">Detailed Guide</a>


=== Usage of Vimeo.com API ===

Vimeify plugin utilizes Vimeo.com API to upload videos to the Vimeo.com service.

Please make sure you read and agree with Vimeo's <a href="https://vimeo.com/terms">Terms</a> and <a href="https://vimeo.com/privacy">Privacy</a> policies.

=== Features  ===

* Upload videos from the Dashboard > Vimeify > Add New screen
* Upload videos from the Gutenberg editor
* Upload videos from the Classic/TinyMCE editor (**NEW!**)
* Manage video folders
* Manage video view privacy
* Manage video embed privacy
* Video list block for Gutenberg
* Video list block for Elementor
* Video list block for Bricks
* Responsive video player embeds
* **"Media > Vimeo"** page is accessible by the users that have the capability upload_files (Author, Editor, Administrators by default)
* **"Settings > Vimeo"** page is accessible by the users that have the capability manage_options (Administrators by default)
* Shortcode available [vimeify_video id="the_vimeo_id"]
* Useful API information and tips in the "Vimeify > Settings > Status"
* Potential problem detection tool in "Vimeify > Settings" page
* Thumbnails support

=== Acknowledgements ===

Vimeify uses the following open-source libraries:

* <a href="https://github.com/tus/tus-js-client">tus-js-client</a>
* <a href="https://github.com/sweetalert2/sweetalert2">sweetlaert2</a>
* <a href="https://github.com/dropzone/dropzone">dropzone</a>

== Installation ==

= Plugin Installation =

* Download the plugin from the WordPress.org repository
* Go to your WordPress Dashboard, navigate to Plugins > Add Plugin and upload the zip file you downloaded.
* Set up your preferences and API credentials from Settings > Vimeo
* Upload videos from Media > Vimeo or the editor

= Plugin Configuration =

* Go to <a target="_blank" href="https://developer.vimeo.com/">Vimeo Developer Portal</a> sign up and "Create App"
* Navigate to My Apps in developer portal, click the app you created
* You need to obtain the following keys and save them in the "Settings > Vimeo" page:
* Client ID: Copy the code from "Client Identifier"
* Client Secret: Copy the code that is shown in the "Client Secrets" area
* Access Token: Click "Generate an access token", select "Authenticated" and select the following scopes: "Public, Private, Edit, Upload, Delete, Create, Interact, Video Files"
* Done, make sure you saved those in Vimeo settings page and try to upload your first video.

= If you have any question feel free to get in touch =

== Frequently Asked Questions ==

= Can i use it without Client ID, Client Secret or Access Token? =

No, you must have Client ID, Client Secret and Access Token with the required scopes/permissions. Please check **Screenshot #5** for more details about the setup.

= Which API scopes are required =

Most of them. Especially if you are using the premium version. So it's best to select the following at least: public, private, create, edit, delete, upload, video_files, interact

= Do I need to do any tweaks to the hosting configuration for bigger files ? =

The files are streamed directly from your browser to Vimeo using the TUS protocol, so the upload process doesn't rely on the server where your site is hosted anymore. Therefore no need to adjust any settings.

== Changelog ==

= Version 1.0.0-beta1 =

* Initial version

