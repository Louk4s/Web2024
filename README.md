# Web2024
## University Web Project.  Academic Year: 2023-2024 Winter Semester


The objective of this project was to create a web application to manage tasks, rescuer assignments and citizen requests using technologies such as PHP, JavaScript, MySQL and HTML. 
The application features an interactive map that displays rescuers, tasks (offers/requests) and active tasks assigned to vehicles. 
The map includes features such as index grouping and filtering options to display specific data.


The libraries used include Leaflet.js for rendering the map and MySQL for database management. The application is fully integrated with the backend, providing real-time data updates. 

Also, a version control system specific to Git was used to control files and optimize team collaboration.

Note: Attached is a sql file ("disaster_database.sql") which contains both the code to create the tables and the database in general and the commands to import the data. 

At the same time, strict rules of organisation were followed. Recognizing the scope of the project, we decided to separate our files into folders. The main folder (root folder) contains the main files, such as login.php and db_connect.php, from which the system derives the required data. After successful login, the system redirects users to the correct page via dashboards.php, which is located in the dashboards folder. The actions folder contains PHP files related to specific functions, such as accept_task.php, and HTML pages.
We decided to embed the HTML code in the PHP files, while the JavaScript code is organized in the scripts folder, and the CSS code in the style folder, with the corresponding references for proper operation.



Keywords: web development, PHP, MySQL, Leaflet.js, task management, map clustering, Version Control Systems, Git
