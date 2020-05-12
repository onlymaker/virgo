drop table if exists virgo_product;
create table virgo_product
(
    id          bigint unsigned primary key auto_increment,
    sku         varchar(50),
    size        varchar(50),
    image       varchar(500),
    images      text,
    last        varchar(500),
    fabric_1    varchar(500),
    fabric_2    varchar(500),
    fabric_3    varchar(500),
    lining_1    varchar(500),
    lining_2    varchar(500),
    lining_3    varchar(500),
    platform    varchar(500),
    heel        varchar(500),
    surround      varchar(500),
    midsole     varchar(500),
    outsole     varchar(500),
    insole      varchar(500),
    lace        varchar(500),
    accessory_1 varchar(500),
    accessory_2 varchar(500),
    price_1     int unsigned default 0 comment 'in cent',
    price_2     int unsigned default 0 comment 'in cent',
    template    varchar(500),
    process_1   varchar(500),
    process_2   varchar(500),
    third_party varchar(500),
    update_date date,
    index (sku),
    index (size),
    index (update_date)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop trigger if exists tri_virgo_product;
create trigger tri_virgo_product
    before update
    on virgo_product
    for each row set new.update_date = current_date;

drop table if exists virgo_material;
create table virgo_material
(
    id       bigint unsigned primary key auto_increment,
    name     varchar(200),
    type     varchar(200),
    location varchar(100),
    quantity int unsigned default 0,
    price    int unsigned default 0 comment 'in cent',
    supplier varchar(200),
    tech     varchar(500),
    index (name),
    index (type)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop table if exists virgo_material_threshold;
create table virgo_material_threshold
(
    id        bigint unsigned primary key auto_increment,
    name      varchar(200),
    value     varchar(200),
    threshold tinyint unsigned default 1,
    index (name)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;
insert into virgo_material_threshold (name, value)
values ("fabric_1", "面料1"),
       ("fabric_2", "面料2"),
       ("fabric_3", "面料3"),
       ("lining_1", "内里1"),
       ("lining_2", "内里2"),
       ("lining_3", "内里3"),
       ("platform", "水台"),
       ("heel", "跟"),
       ("surround", "包料"),
       ("midsole", "中底"),
       ("outsole", "大底"),
       ("insole", "膛底"),
       ("lace", "鞋带"),
       ("accessory_1", "扣件1"),
       ("accessory_2", "扣件2");

drop table if exists virgo_material_order;
create table virgo_material_order
(
    id             bigint unsigned primary key auto_increment,
    name           varchar(200),
    type           varchar(200),
    plan_price     int unsigned          default 0 comment 'in cent',
    plan_quantity  int unsigned          default 0,
    plan_supplier  varchar(200),
    final_price    int unsigned          default 0 comment 'in cent',
    final_quantity int unsigned          default 0,
    final_supplier varchar(200),
    status         tinyint unsigned      default 0 comment '0:created,1:finish,2:cancel',
    serial         varchar(100) not null,
    create_time    timestamp    not null default current_timestamp,
    index (name),
    index (type)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop table if exists virgo_material_history;
create table virgo_material_history
(
    id        bigint unsigned primary key auto_increment,
    name      varchar(200),
    type      varchar(200),
    event     json,
    previous  json,
    current   json,
    timestamp timestamp not null default current_timestamp
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop table if exists virgo_order;
create table virgo_order
(
    id            bigint unsigned primary key auto_increment,
    sku           varchar(50),
    size          varchar(50),
    image         varchar(500),
    quantity      int unsigned       default 1,
    order_type    tinyint unsigned   default 0 comment '0:single,1:volume',
    order_sponsor varchar(50),
    order_channel varchar(50),
    order_number  varchar(50),
    status        tinyint unsigned   default 0,
    create_time   timestamp not null default current_timestamp,
    update_time   timestamp default '2020-01-01',
    index (sku),
    index (size),
    index (order_number),
    index (create_time)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop trigger if exists tri_virgo_order;
create trigger tri_virgo_order
    before update
    on virgo_order
    for each row set new.update_time = current_timestamp;

drop table if exists virgo_order_history;
create table virgo_order_history
(
    id            bigint unsigned primary key auto_increment,
    sku           varchar(50),
    size          varchar(50),
    image         varchar(500),
    quantity      int unsigned       default 1,
    order_type    tinyint unsigned   default 0 comment '0:single,1:volume',
    order_sponsor varchar(50),
    order_channel varchar(50),
    order_number  varchar(50),
    status        tinyint unsigned   default 0,
    description   text,
    create_time   timestamp not null default current_timestamp,
    index (sku),
    index (size),
    index (order_number),
    index (create_time)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

drop table if exists virgo_order_qc;
create table virgo_order_qc
(
    id            bigint unsigned primary key auto_increment,
    sku           varchar(50),
    image         varchar(500),
    quantity      int unsigned       default 1,
    rejected      int unsigned       default 1,
    order_type    tinyint unsigned   default 0 comment '0:single,1:volume',
    order_sponsor varchar(50),
    order_channel varchar(50),
    order_number  varchar(50),
    description   text,
    create_time   timestamp not null default current_timestamp,
    index (sku),
    index (order_number),
    index (create_time)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;