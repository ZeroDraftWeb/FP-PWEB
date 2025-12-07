**Updated Project Summary: GDD Organizer (Game Design Document Hub)**

**1. System Logic & Purpose**
The **GDD Organizer** is a unified dashboard for indie game developers who currently manage game assets, narrative ideas, and character stats across scattered tools (folders, Notepad, Excel). It centralizes the entire pre-production workflow into a single web platform.

- **Asset Gallery** – A resource management module for uploading, organizing, and previewing game sprites/images (CRUD operations). Assets stored here can be referenced in other sections.
- **Character Stat Builder** – A simulation module for defining and balancing character attributes using sliders for intuitive stat adjustment (e.g., high HP vs. low Speed).
- **Interactive Storyline** – The core logic flow module, allowing visual creation of branching narratives through node-based editors.
- **Export as PDF** – A reporting feature that compiles all project data into a printable, shareable Game Design Document.

**2. UI/UX Design Rationale**
- **Theme**: Dark mode with an industrial aesthetic to match common developer environments (e.g., Godot, VS Code), reducing eye strain and feeling familiar.
- **Layout**: Modular card-based dashboard allowing focused editing within sections while maintaining an overview of the entire project.
- **Interactive Components**:
  - Sliders for stats provide more intuitive balancing than numeric input.
  - Node-based storyline visualization simplifies understanding of complex narrative branches compared to text-heavy outlines.

**3. Updated Technical Implementation Plan**
**Technology Stack:**
- **Frontend**: Native HTML, CSS, JavaScript with Bootstrap 5 for responsive styling and UI components.
- **Backend**: PHP Native (no frameworks) with MySQL database.
- **Additional Libraries**:
  - JavaScript libraries for interactive features (e.g., vis.js or plain JS for node-based storyline).
  - TCPDF or FPDF for PDF generation in PHP.
  - jQuery (optional) for simplified AJAX calls.

**Database Schema (MySQL):**
- `users` – Authentication (id, username, password_hash, created_at)
- `projects` – Project metadata (id, user_id, title, created_at, last_opened)
- `assets` – Uploaded image paths (id, project_id, file_path, category, uploaded_at)
- `characters` – Character stats (id, project_id, name, hp, attack, speed, image_id)
- `story_nodes` – Narrative nodes (id, project_id, title, content, position_x, position_y, connections_json)

**Data Flow:**
- Asset uploads use PHP's `$_FILES` handling to store files in a designated directory, with paths saved in the DB.
- Character stats are saved via AJAX (Fetch API or XMLHttpRequest) to PHP endpoints for CRUD operations.
- Story nodes and connections are stored as JSON in a MySQL JSON column or text field.
- PDF export uses TCPDF/FPDF to generate documents by fetching data from the database.

**4. Authentication System**
- Session-based authentication for user management and security.
- User registration and login with secure password hashing.
- Role-based access control for different user functionalities.

**5. Page Structure & Implementation**
The web app will consist of 5 main pages built with Bootstrap for layout and styling:

1. **Login / Sign Up Page**
   - Bootstrap forms with validation.
   - PHP session-based authentication.

2. **Home Page**
   - Dashboard displaying user's projects using Bootstrap cards.
   - "Recently Opened" section showing last 5 projects (tracked via `last_opened` timestamp).

3. **Edit Project Page**
   - Main dashboard with modular Bootstrap cards for each section (Asset Gallery, Character Builder, Storyline).
   - Interactive components built with vanilla JavaScript:
     - Drag-and-drop for asset management.
     - Range sliders for character stats.
     - Canvas-based node editor for storyline.

4. **Profile Page**
   - User account management with Bootstrap form components.
   - Project statistics and user information.

5. **Admin Page** (Optional)
   - User management panel for administrators.
   - User listing and management capabilities.

**6. Security Considerations**
- Password hashing using PHP's `password_hash()` and `password_verify()`.
- Prepared statements for all database queries to prevent SQL injection.
- File upload validation (type, size, malware scanning).
- Session security with regeneration and proper timeouts.

**7. Deployment Considerations**
- Standard LAMP/LEMP stack deployment.
- File storage organization with proper permissions.
- Session cleanup tasks and database maintenance.