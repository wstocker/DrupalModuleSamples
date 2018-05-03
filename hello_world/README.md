# ‘Hello World’ module notes pre-development:
  
  - Create a custom block and attach it to the right side bar of the page
    where content type is equal to ‘Hello World Article’.
  - Figure out how to create a custom block using D8.
  - To get the ‘Hello Articles’ tagged with only enabled sections field: 
    Form an entity query that gets all the nodes that are A. of that content type,
    and B. are tagged with that vocabulary where the boolean field for the term 
    selected is equal to TRUE.
  - Create links for results, and render in custom block template.
  - Use custom CSS for styling, included only on pages where the content type
    is a ‘Hello World Article’. 

# ‘Hello World’ module Implementation:

1. Created custom block using the block plugin. Class name: HelloWorldBlock.

2. Within the build method of the class HelloWorldBlock:

   - Get a container of the taxonomy term entities that have the 
     vocabulary 'sections'.
   - Create an array of the term ids that have the vocabulary field enabled, 
     set to true.
   - Get a container of nodes that have the property field 'sections' using term id 
     array to get results. That way we are are only getting the entities that
     have field ‘sections’ enabled.
   - Loop through the result entities and create an absolute link to each one.
   - Return ‘hello world’ custom block theme with the populated template variables.

3. In the ‘Hello World’ module file: 
   
   # Implemented hook_theme function:
   - I set up theme values for custom ‘hello world block’ template.
   - I set up theme values for custom ‘content title’ template
   - I set up theme values for custom page ‘Hello World Article’ 
     content type template
   - Copied the block.html.twig template from Bartik theme, and adjusted it in 
     /templates/templates/hello-world-block.html.twig
   - Within the new ‘hello world block’ template, loop through each link 
     that was created from HelloWorldBlock.
   - Saved custom content title template in /templates/content-title.twig,
     and added the text for the headline that appears on all 
     ‘Hello World Article’ pages.

   #Implemented hook_preprocess_page__CONTENT_TYPE function:
   - Use the plugin manager service to render 'hello world block' from the module’s 
     block plugin.
   - Set the rendered block to page template variables to be used in the 
     page template.

   #Implemented hook_node_view function:
   - First checks if the content type is a ‘Hello World Article’.
   - If so, it renders the custom theme I created for ‘content title’ and prepends that to 
     the node body.
   - Attach a custom CSS library here as well, since we only need custom 
     styles for ‘Hello World Article’ content types.

   #Implemented hook_theme_suggestions_HOOK_alter function:
   - If the route match method returns a node, suggest a page template with a 
     matching content type name.
   - Copied the page.html.twig template from Bartik theme, and adjusted it for 
     /templates/page--hello-world-article.html.twig
   - Within page--hello-world-article.html.twig template, I removed the if statement 
     surrounding the second side bar, since it should always be visible.
   - Added my custom block I created from hook_preprocess_page function into 
     the second sidebar of the custom page template: {{ page.hello_world_block }}.
   - Tweaked my custom CSS so that the title of the ‘hello world block’ is bold.
     So the headline visible on all ‘Hello World Article’ pages is italic.


