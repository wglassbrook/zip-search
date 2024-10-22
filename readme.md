#Zip Search  

The Zip Search plugin allows you to upload ZIP Code related data that will be searchable by your site’s visitors.

##Overview  

To use, simply upload your pre-formatted CSV using the upload field on the Zip Search options page within the WordPress dashboard. Once uploaded, a table of your data will be displayed. Please review this table for accuracy. This is the data that will be displayed to visitors when a zip code is searched for. If you find inaccuracies within the table, please return to your Excel spreadsheet to make revisions and then re-upload. The plugin will always use the most recently uploaded CSV.

##Formatting Your CSV  

When creating the CSV for data uploads, please adhere to the following rules:

- Your CSV file must be comma separated (Default when exporting from Excel).
- Ensure the three columns are Zip, Organization, and URL.
- Organization URLs must be the complete URL, including the leading http:// or https://, otherwise the link will result in a 404 error.
- It’s okay to have multiple Organizations per ZIP Code; however, this utility does not ensure that your row data is unique. Make sure this is done in the CSV before upload.
- All data is required. You must have each of the three cells in a row complete (no empty Zip, Organization or URL cells).
- An example of a valid, pre-formatted CSV file is included within the root of this plugin (zip_data_example.csv).

##Shortcode Usage  

The search field will display wherever you use the shortcode <pre>[zip-search]</pre> anywhere within your content. Results will display directly under the search field.
