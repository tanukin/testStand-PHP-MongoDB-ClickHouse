Test stand PHP + MongoDB + ClickHouse
=====================================
Test bench for performance comparison aggregation requests in MongoDB and ClickHouse

Settings
---
Check the file: src/Config/config.yml
~~~
mongo:
  host: "mongo"
  port: 27017
  dbname: "teststand"
  collection: "teststand"

clickhouse:
  host: "clickhouse"
  port: 8123
  username: "default"
  password: ""
  dbname: "teststand"
  tablename: "teststand"
~~~

Starting the environment
---
~~~
docker-compose up -d
~~~
Run the test on the test stand
---
```
docker update --cpus="##" --memory="##" --memory-swap="##" test-stand mongo clikhouse
```
~~~
1) docker exec -it test-stand bash
2) ./bin/console app:TestStand
~~~

Results
---
#### Test №1
CPU | Memory | Swap
--- |---    |  ---
1   |1024 mb | 0 

![Tets number 1](https://user-images.githubusercontent.com/16325359/39124741-34c165c8-4705-11e8-9264-3a7c7248866a.png)

#### Test №2
CPU | Memory | Swap
--- |---    |  ---
2   |2048 mb | 0 

![Tets number 2](https://user-images.githubusercontent.com/16325359/39124751-44e262ea-4705-11e8-9782-2264b6d48bc1.png)

#### Test №3
CPU | Memory | Swap
--- |---    |  ---
3   |4096 mb | 0 

![Tets number 3](https://user-images.githubusercontent.com/16325359/39124756-4bd90e32-4705-11e8-96b4-9bc7943490b9.png)

#### Conclusion

Can be seen from the results, the MongoDB records data in the database faster than the ClickHouse, but the reading speed of the ClickHouse is faster than the MongoDB.