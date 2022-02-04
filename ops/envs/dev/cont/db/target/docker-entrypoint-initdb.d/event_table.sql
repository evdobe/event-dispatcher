
--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.24
-- Dumped by pg_dump version 9.6.24

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: event; Type: TABLE; Schema: public; Owner: adbuser
--

CREATE TABLE public.event (
    id integer NOT NULL,
    "name" text NOT NULL,
    channel text NOT NULL,
    correlation_id integer NOT NULL,
    aggregate_id integer NOT NULL,
    aggregate_version integer NOT NULL,
    data jsonb NOT NULL,
    "timestamp" timestamp(3) without time zone DEFAULT now() NOT NULL,
    dispatched boolean DEFAULT false NOT NULL,
    dispatched_at timestamp(3) without time zone,
    received_at timestamp(3) without time zone,
    projected boolean DEFAULT false NOT NULL
);


ALTER TABLE public.event OWNER TO adbuser;

--
-- Name: event_id_seq; Type: SEQUENCE; Schema: public; Owner: adbuser
--

CREATE SEQUENCE public.event_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE public.event_id_seq OWNER TO adbuser;

--
-- Name: event_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: adbuser
--

ALTER SEQUENCE public.event_id_seq OWNED BY public.event.id;


--
-- Name: event id; Type: DEFAULT; Schema: public; Owner: adbuser
--

ALTER TABLE ONLY public.event ALTER COLUMN id SET DEFAULT nextval('public.event_id_seq'::regclass);


--
-- Name: event event_pkey; Type: CONSTRAINT; Schema: public; Owner: adbuser
--

ALTER TABLE ONLY public.event
    ADD CONSTRAINT event_pkey PRIMARY KEY (id);


--
-- PostgreSQL database dump complete
--

