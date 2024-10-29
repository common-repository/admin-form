=== ADFO – Custom data in admin dashboard ===
Contributors: giuliopanda
Tags: database, datatables, template engine, data table, data tables
Requires at least: 5.9
Tested up to: 6.5
Requires PHP: 7.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Stable tag: 1.9.1

Allow your customers to manage new data sets for your site.

== Description ==
If you're a website developer and you need to extend the functionality of WordPress by creating new data structures, this plugin is for you.

Admin Form allows you to create data collections by connecting directly to your WordPress database. With this plugin, you can easily create new data structures or manage and filter existing tables in the database. For instance, you can streamline the process of developing new plugins by automating the entire data entry administration section.

Using shortcodes, you can quickly display HTML tables on your site with search results, column sorting, and page breakdowns. Alternatively, you can design your own HTML and use special shortcodes to input data. Admin Form also includes a complete template engine.

Our comprehensive documentation provides clear and concise instructions on how to use the plugin to its fullest extent. Additionally, the plugin offers flexible customization options, so you can tailor it to your specific needs. Whether you need to manage existing data or create entirely new structures, our plugin can help you accomplish your goals quickly and efficiently. 

== Key Features ==

With Admin Form, you can:

- Create data tables for backend use, including forms with more than 20 data types and an advanced Excel-style search system
- Choose the fields to display and how to display them
- Delete selected data or choose to delete all filtered data (PRO version only)
- Download selected data or all filtered data (PRO version only)
- Choose the roles of who can edit the table
- Use LOOKUP field to show data from other tables (PRO version only)
- Create and edit complex data structures from MySQL queries (PRO version only)
- Publish data in tables with customizable colors, pagination, and search/sort fields
- Show details of single records in popups
- Customize data display using a powerful template engine with attributes, operators, and shortcodes
- Work with PHP hooks and filters, and use a class with functions to manage data from the code
- Create custom post types, generating new lists of data and detail pages
- Add custom fields to post, user, and comment tables, as well as other tables that have a linked "meta" table
- Enjoy advanced management in table creation with the PRO version
I- mport and export data in MySQL or CSV format with the PRO version
- Download the free PRO version at https://github.com/giuliopanda/admin-form-pro/releases/ to access even more features, such as calculated fields and the ability to create query forms.
- Choose the color, the type of pagination, whether to show the search or the ability to sort the fields.
- Show the detail of single records in popup.
- Customize the view through a custom template engine.

We hope you find Admin Form to be a useful tool for your WordPress development needs!

== TEMPLATE ENGINE ==
You have at your disposal a powerful template engine similar to wordpress shortcodes.
You can customize the data display through attributes. For example to make text uppercase:
` 
[%item.Title uppercase]
`
You can control data flows through the most used operators (if, for, break, while, math...)
For example, to check if a user is logged in you can write:
` 
[^IF [^user]==""] you are not logged in
[^else]  You are logged in [^endif]
`
Or pull the data directly into a post via the [adfo_tmpl] shortcode
`
[adfo_tmpl]
<ul>
    [^FOR EACH=[^POST TYPE=post]]
        <li>[%item.title_link]</li>
    [^ENDFOR]
</ul>
[/adfo_tmpl]
`
== PHP ==
Obviously you have a set of hooks and filters available for working in php, but you also have a class available with a set of functions to manage data from the code. You will find all the documentation inside the plugin.

== POST TYPE SUPPORT ==
Custom post types can be created. These are handled with the plugin's display and insertion system, thus focusing on data entry rather than content formatting. Using post_types, it is possible to generate not only new lists of data, but also the detail of individual content opened on new pages.

== METADATA SUPPORT ==
For post, user, comment tables and all tables that have a linked "meta" table, Admin Form allows you to add custom fields to the table you are editing.

== MULTI ROW FOR POST, USER, MEDIA AND LOOKUP SUPPORT ==
You can create fields that can save multiple data such as user lists, media or posts.


== PRO VERSION ==
There is also a PRO version that you can download for free!

The pro version adds:
- [The calculated fields](https://github.com/giuliopanda/admin-form-pro/wiki/Calculated-fields).
- [The lookups fields](https://github.com/giuliopanda/admin-form-pro/wiki/LOOKUP).
- A system for managing tables through mysql queries
- The ability to create query forms (LAB).
- Advanced management in the creation of tables
- [Import and export of data in mysql / csv](https://github.com/giuliopanda/admin-form-pro/wiki/Import-Export).

With the pro version you can directly manage the WordPress database by creating queries like on phpmyadmin or adminer.
Some differences are:
- Greater integration with WordPress allows you to avoid accidental changes to WordPress core tables.
- The replace system supports serialized fields
- You can edit the query with the help of a series of dedicated tools
- For each column you have a menu of options inspired by the Excel system
- The ability to edit multiple tables at the same time. in fact, it is possible to edit the fields extracted from the query independently of the tables to which it refers.
For example:

` 
SELECT post.post_title, m01.meta_value AS test, m02.meta_value AS test2 FROM `wp_posts` post LEFT JOIN wp_postmeta m01 ON post.ID = m01.post_id AND m01.meta_key = 'test' LEFT JOIN wp_postmeta m02 ON post.ID = m02.post_id AND m02.meta_key = 'test2'
` 

Allows you to edit both the test field and the test2 field

the link to the project:  [https://github.com/giuliopanda/admin-form-pro/releases/](https://github.com/giuliopanda/admin-form-pro/releases/)

== Installation ==
Download the plugin and activate it.

== changelog ==


= 1.9.1 2024-05-03 - Security fix =
* FIXBUG: Security fix: adding nonce verification to the form.
* FIXBUG: Security fix: sanitizing and escaping the dbp_id value.
Thanks to wordfence.com for the advice.

= 1.9.0 2024-03-03 - Multifields =
* NEW: Multifields have been developed for the post, user, lookup and mediagallery fields. These are saved in a json field.
* IMPROVEMENT: Added json attribute to the template engine
* IMPROVEMENT: csv download handles list data better, shows complete texts, correct html and does not show unnecessary links or added html in columns.
* IMPROVEMENT: Left join autocompletes now show only the actual values i.e. metavalues only show the values of the chosen metakey.
* IMPROVEMENT: Improved communication in the order field.
* FIXBUG: When you download or export it now extracts a series of useless columns (the val ones)!
* FIXBUG: Notice: Function register_post_type was called incorrectly.
* FIXBUG: Pagination on lists on the last page sometimes disappears (wp_posts_with_metakey)
* FIXBUG: in form-admin fix some Deprecated in php 8.x
* FIXBUG: in list-form if I create a new field the show wraps! layout bug 
* FIXBUG pinacode [^USER] returned empty even if logged in
* FIXBUG [^IF [%a] == "" ] if [%a] was not set it did not return true
* FIXBUG [^IF [^user] == ""] if you were not logged in it didn't return true
* FIXBUG export sql error in fields where there was a %
* FIXBUGS various deprecated notice
* FIXBUG creation field missing ORDER field

= 1.8.4 2023-11-27 - FIXBUG =
* In amministrazione se provi a collegare uno shortcode di una tabella in una colonna custom dava fatal error perché non trovava search

= 1.8.3 2023-04-14 - FIXBUG = 
* BUGFIX: The saving of the fields of the form did not work

= 1.8.2 2023-03-15 - FIXBUG = 
* BUGFIX: The pagination had disappeared, I apologize for the bug.

= 1.8.1 2023-03-13 - FIXBUG = 
* BUGFIX: Input in list field
* BUGFIX: In the form, there was very limited number of metadata that could be entered without changing max_input_vars
* BUGFIX: PHP errors were being shown

= 1.8.0 2023-03-08 - Edit fields in a list = 
* NEW: Editing fields directly in lists. It is now possible to edit data directly from the list through the Edit Field columns. The following have been implemented: checkboxes, input, select fields and a sort field.
* NEW Frontend Export. Now you can show a button for exporting data to the frontend. If you want to manage from code, ADFO:draw_export_btn function has been added which draws buttons to export a table in the frontend to csv or sql. The function [^BTN_EXPORT] has been added from the template engine.
* NEW: Adds the new functions for the template engine [^GET_SINGLE] and [^GET_DETAIL] [^GET_TOTAL]
* NEW: form field ORDER: the field is of numeric type. By default a number that is always higher than the previous one is proposed.  
* NEW: Added CODE TAB with examples related to the list in use.
* IMPROVEMENT: ADFO::get_data if not passed limit and order parameters takes default parameters.
* IMPROVEMENT: Improved page loading performance. The query to calculate the total number of records has been optimized.
* IMPROVEMENT: Ability to randomly sort table data.
* IMPROVEMENT: redesigned the sort buttons.
* BUGFIX: Loss of list settings when saving the frontend setting.
* BUGFIX: Inhibited the ability to create fields with only numbers.
* BUGFIX PRO: Always appear show all text even when query gives error.
* BUGFIX PRO: If a query gave error the double editor would appear!
* BUGFIX PRO: List export and subsequent import used to fail for complex queries or some types of fields, especially calculated fields which are now added to RAW export. 
* BUGFIX: If a status field is present it could not be found in the frontend.
* BUGFIX: Default value for DATE, DATETIME and TIME now is converted to date in the correct format according to the field type.
* BUGFIX: Dates in javascript are now handled as timestamps. 
* BUGFIX: The invalid fields already appeared red when you loaded the form (before you did the submit).

= 1.7.0 2023-02-16 - POST_TYPE = 
* NEW: Post_type management. When creating a new form, a post_type can be created directly or a new form linked to the post table can be created by filtering directly for a post_type. Registration of a new post_type can be customized within the setting tab.
When a module is linked to a post_type the post_type filter is imposed directly in the query.
* NEW: Status field. The status field manages published, draft, and trash records. 
* IMPROVEMENT: author field. Now if a field is set as author it is possible to manage roles to allow some users to see and edit only their own records.
* IMPROVEMENT: Optimization of saving settings.
* IMPROVEMENT: Default configuration for posts when creating a new list.
* IMPROVEMENT: IDs are hidden in the frontend detail by default.
* IMPROVEMENT: new php ADFO::get_single function to have the detail display of a record.
* IMPROVEMENT: of tab form and list view formatting display and sorting.
* REMOVE filter legacy mode. apply_filters( 'dbp_ ...
* BUGFIX the author field is overwritten when editing.
* BUGFIX: even if a list is put in trash the shortcodes work !!! [adfo_list id=xxx]
* BUGFIX: on sorting form fields within the same table. The preview of the field after you release is wrong it is like pasting in wrong nesting or carrying the wrong preview.

= 1.6.0 2023-22-01 - FIXBUG = 
* NEW: Added Time field and template engine attributes: timenum-to-timestr, timestr-to-timenum and time-to-hour
* NEW (PRO) in the datapress main page added a box that shows the running queries and allows you to remove them
* FIXBUG (PRO) show table in import data list 
* IMPROVEMENT: Limited metadata function. Metadata must be unique. There can be only one metadata for each meta_key. During the save if I find more metadata for the same key I delete them.
* IMPROVEMENT: (TAB FORM) the test of the calculated fields does not work with data which however works in production. Added an information box if using [%data.
* IMPROVEMENT: (TAB FORM) option in select: if there are no values I set them equal to the labels
* IMPROVEMENT: Changed the default setting of the frontend of the tables.
* IMPROVEMENT: Rebuild of post and user fields on lists. They now display the post title or the user's nicename. Search now works on user title or name instead of IDs. However, it is not possible to sort the list by a post or user field. This will not be developed because if you change the data type of the list the query will give an error.
* IMPROVEMENT: (LIST view formatting) On post and user type columns I no longer choose which information to show, it's always the title or username, but I've added the option to automatically create the link to the article or author's page. Attention this change generates a small incompatibility with previous versions. If you are using the post or user field check out the new functionality. If you wanted to link to other fields, use the lookup field instead.
* FIXBUG Added checking on date fields when they are invalid.
* FIXBUG: (page action=list-sql-edit) When I save it hides the primary keys in the lists.
* FIXBUG: on import for user post and lookup fields
* FIXBUG: FRONTEND when you press enter and you have the focus on the search even if it is in ajax it sends the data in get
* FIXBUG: (TAB FORM) When you created a new field it didn't allow dragging it to sort.
* FIXBUG: In frontend tables, filters with values populated by particular shortcodes (like [^current_post.id] ) were forgotten in ajax calls for paging, or searching.

= 1.5.0 2023-01-08 =
= 1.4.0 2022-12-15 =
= 1.3.0 2022-12-07 =
= 1.2.0 2022-11-23 =
= 1.1.0 2022-11-17 =
= 1.0.1 2022-11-12 =


== screenshot ==
1.
2.
3.
4.
5.
6.
7.
