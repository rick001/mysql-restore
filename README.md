Chunked File Upload and SQL Restore with PHP and Plupload
Overview

This repository provides a solution for uploading large SQL or zipped SQL files in chunks using Plupload and PHP. It then restores the database in chunks to avoid timeouts.
Features

    Upload large files in chunks using Plupload.
    Unzip SQL files if needed.
    Restore the database in manageable chunks.
    Accepts user input for database credentials.

Prerequisites

    Web server (Apache/Nginx)
    PHP
    MySQL

Installation

    Clone the Repository

    bash

    git clone https://github.com/rick001/mysql-restore.git
    cd mysql-restore

    Set Up the Web Server
        Place the repository files in your web server's root directory.
        Ensure the uploads directory is writable by the web server.

    Configure PHP
        Ensure upload_max_filesize, post_max_size, max_execution_time, and memory_limit in php.ini are set to accommodate large files.
        Restart the PHP service after making changes to php.ini.

Usage

    Access the Form
        Open the form in your web browser (e.g., http://your-server/index.html).

    Fill Out the Form
        Enter your database credentials.
        Select the SQL or zipped SQL file to upload.

    Upload and Restore
        Click the upload button.
        The file will be uploaded in chunks, unzipped if necessary, and restored to the database in chunks.

License

This project is licensed under the MIT License. See the LICENSE file for details.
Contributions

Contributions are welcome! Please fork the repository and create a pull request with your changes.
Support

For support or questions, please open an issue in this repository.
