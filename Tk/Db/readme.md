
## Documentation



### Tables

The following outlines the new changes:

 - `id` field renamed to `user_id` or `{table}_id` this is for future sql queries
 - The type `TIMESTAMP` will now be used for most if not all date fields to help with internationalization
 - `created` and `modified` will now be updated in the DB and not in the PHP code.

```mysql
CREATE TABLE IF NOT EXISTS user
(
  user_id     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  uid         VARCHAR(128) DEFAULT ''    NOT NULL,
  type        VARCHAR(32)  DEFAULT ''    NOT NULL,
  `username`  VARCHAR(64)  DEFAULT ''    NOT NULL,
  `password`  VARCHAR(128) default ''    not null,
  name_first  VARCHAR(128) DEFAULT ''    NOT NULL,
  name_last   VARCHAR(128) DEFAULT ''    NOT NULL,
  email       VARCHAR(255) DEFAULT ''    NOT NULL,
  del         BOOL         DEFAULT FALSE NOT NULL,
  modified    TIMESTAMP    ON UPDATE CURRENT_TIMESTAMP,
  created     TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
  KEY (`username`),
  KEY (`email`)
) ENGINE=InnoDB;

```

We are now using the table name in the primary ID key field. 
As in the above example `user_id` replaces `id`.

