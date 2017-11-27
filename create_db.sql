CREATE DATABASE meteo;

CREATE TABLE public.forecast
(
  forecast_id       INTEGER PRIMARY KEY NOT NULL,
  partner_city_code VARCHAR,
  date              TIMESTAMP           NOT NULL,
  cloud             SMALLINT  DEFAULT 0,
  precip            SMALLINT  DEFAULT 0,
  temp              SMALLINT  DEFAULT 0,
  date_modify       TIMESTAMP DEFAULT now(),
  wind_direction    SMALLINT  DEFAULT 0,
  wind_speed        SMALLINT  DEFAULT 0,
  pressure          SMALLINT  DEFAULT 0,
  humidity          SMALLINT  DEFAULT 0,
  water_t           SMALLINT  DEFAULT 0,
  is_felt_t         SMALLINT  DEFAULT 0
);

CREATE UNIQUE INDEX forecast_pkey
  ON public.forecast (forecast_id);

CREATE UNIQUE INDEX forecast_date_uindex
  ON public.forecast (date);