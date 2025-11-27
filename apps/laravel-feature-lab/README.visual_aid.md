                 +----------------+
                 |   localhost    |
                 |                |
                 | 8080 -> Nginx  |
                 | 8025 -> Mailpit|
                 | 5173 -> Vite   |
                 +--------+-------+
                          |
                          | appnet (custom bridge network)
                          |
  -----------------------------------------------------------------
  |            |               |              |                   |
  v            v               v              v                   v
+------+   +---------+     +---------+   +---------+         +---------+
| app  |   | workspace|     | nginx   |   | node    |         | scheduler|
| PHP- |   | dev tools|     | web srv |   | optional|         | cron    |
| FPM  |   | composer |     |         |   |         |         |        |
+------+   | +Node+Vite|    +---------+   +---------+         +---------+
   |       +---------+          |                |
   |            |               |                |
   v            |               |                |
+------+        |               |                |
| queue|        |               |                |
| PHP  |        |               |                |
+------+        |               |                |
                |               |                |
  ----------------- appnet ----------------------------
                |
       +--------+--------+
       v                 v
    +-------+         +-------+
    | redis |         |postgres|
    +-------+         +-------+
       |                 |
       v                 v
    PHP cache /       PHP DB connection
    queues

+------------------+
| Meilisearch      |
+------------------+
| search engine    |
| exposed 7700     |
+------------------+

