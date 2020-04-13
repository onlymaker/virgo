drop table if exists virgo_product;
create table virgo_product
(
    id          bigint unsigned primary key auto_increment,
    sku         varchar(50),
    size        varchar(50),
    image       varchar(500),
    last        varchar(500),
    material_1  varchar(500),
    material_2  varchar(500),
    platform    varchar(500),
    heel        varchar(500),
    bottom_1    varchar(500),
    bottom_2    varchar(500),
    bottom_3    varchar(500),
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
    index (name),
    index (type)
) engine = InnoDB
  default charset = utf8mb4
  collate = utf8mb4_unicode_ci;

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
