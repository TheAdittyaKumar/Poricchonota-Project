# Poricchonota

A web-based citizen complaint & community reporting system for public issues (Bangladesh-focused), built as a university database project.

# Project Overview

Poricchonota is a complaint management platform where citizens can report local problems (garbage, drainage, street lights, toilets, etc.) with location + image evidence, and authorized staff (Admin/Engineer) can track, update, and resolve those complaints through role-based dashboards. A community feed + voting system helps prioritize the most important issues.

# Key Features (CRUD-based)
## Citizen Portal

### Sign up / Login / Logout

### Profile view

### Submit complaint (Create) with:

description, category, image upload

map-based latitude/longitude selection

### Dashboard (Read): view own complaints + status

### View complaint details (Read)

### Community Feed (Read):

list all complaints

upvote/downvote issues (Update/Delete vote)

## Staff Portal (Admin + Engineer)

Staff signup / login (role-based)

Engineer dashboard (Read):

view complaints by department responsibility

take action and update complaint progress

Admin dashboard (Read/Manage)

Complaint details page (Update):

status changes

resolution photo + remarks

logs via complaint history/update tables

Delete item (Delete) where needed (admin operations)

## Tech Stack

Backend: PHP (procedural PHP + sessions)

Database: MySQL / MariaDB (phpMyAdmin)

Frontend: HTML + CSS

Client-side logic: JavaScript (Fetch API + JSON responses)

Map Integration: Leaflet.js + OpenStreetMap (click-to-drop marker & capture lat/lng)

Environment: XAMPP (Apache + MySQL)

## Database Design

Included:
✅ MySQL .sql database export
✅ EER Diagram
✅ Relational Schema

Normalization target: 1NF, 2NF, 3NF (your final version removes redundant/derived attributes such as cached vote counts and staff dept duplication in user).

# Repository Structure
PORICCHONOTA-MAIN/
├─ citizen_portal/
│  ├─ citizen_dashboard.php
│  ├─ citizen_login.php
│  ├─ citizen_profile.php
│  ├─ citizen_signup.php
│  ├─ community_feed.php
│  ├─ logout.php
│  ├─ profile.php
│  ├─ submit_complaint.php
│  ├─ view_complaint.php
│  └─ vote_action.php
│
├─ staff_portal/
│  ├─ admin_dashboard.php
│  ├─ complaint_details.php
│  ├─ delete_item.php
│  ├─ engineer_dashboard.php
│  ├─ logout.php
│  ├─ staff_login.php
│  └─ staff_signup.php
│
├─ uploads/                # complaint & resolution images stored here
├─ images/                 # UI/background images
├─ db.php                  # central DB connection file
├─ poricchonota.sql         # database dump (import this)
├─ README.md
└─ (EER + schema files)     # included in repo

# How to Run (XAMPP)
## 1) Setup Project

Install XAMPP

Copy the project folder to:

C:\xampp\htdocs\Poricchonota-main


Start Apache + MySQL from XAMPP Control Panel

## 2) Import Database

Open phpMyAdmin:

http://localhost/phpmyadmin

Create a database named:

poricchonota

Import the SQL file:

poricchonota.sql

## 3) Configure DB Connection

Open db.php and ensure credentials match your local setup (example):

host: localhost

user: root

password: (empty by default in XAMPP)

database: poricchonota

## 4) Open the System in Browser

If your folder name is Poricchonota-main, then:

✅ Citizen Portal

http://localhost/Poricchonota-main/citizen_portal/citizen_login.php

✅ Staff Portal

http://localhost/Poricchonota-main/staff_portal/staff_login.php

(If your folder name is different, replace it in the URL.)

# Uploads / Images

Complaint images and resolution images are stored in:

uploads/

Make sure the folder exists and is writable by Apache (usually fine in XAMPP).

# Security Notes (Academic Project)

Uses prepared statements to reduce SQL injection risk

Uses sessions for authentication and access control

Passwords are handled using hashing (password_hash, password_verify)

# Individual Contribution

I designed and developed the full Poricchonota system (database + frontend + backend) using MySQL, PHP, JavaScript, HTML, and CSS, including role-based portals, normalized schema (up to 3NF), Leaflet/OpenStreetMap map integration, complaint workflow tracking, and community voting with live JSON-based updates.

# Screenshots / Diagrams

This repository includes:

✅ EER Diagram

✅ Relational Schema

✅ Database Export (.sql)

✅ Uploaded complaint image samples (uploads/)
