# MediaWiki ContentTranslation Extension

## Overview
The **ContentTranslation** extension for MediaWiki facilitates the translation of wiki pages using machine translation and various translation tools. This hybrid project integrates:
- A **JavaScript-based frontend** (using Vue.js, Vuex, and composable functions)
- A **PHP-based backend** (integrating with MediaWiki’s APIs and databases)

## Project Structure
### 1. **Frontend Client**
Located in the `app` folder, the frontend consists of:
- **Vue Components**: CXDashboard, SXEditor, SXPublisher, etc.
- **Vuex Store**: Manages application state (`app/src/store`)
- **Composable Functions**: Reusable utilities and logic
- **Plugins & UI Libraries**: Located in `app/lib/mediawiki.ui`
- **Specialized UI Module**: `minT` folder, possibly handling a unique translation interface

### 2. **Backend / Extension Logic**
- Located in the `includes` and `modules` directories
- Handles API requests, translation storage, and interactions with MediaWiki
- Key components:
  - `includes/ActionApi`: Implements custom MediaWiki API actions
  - `includes/DTO`: Data Transfer Objects for handling structured data
  - `includes/Entity`: Models managing translation units and user interactions
  - `modules`: Additional business logic and database interactions

### 3. **External Services & Dependencies**
- **MediaWiki APIs**: Core integration
- **Machine Translation Services**: Used for automated translations
- **Chart.js**: Data visualizations (`lib/chart.js`)
- **Testing Frameworks**: phpunit, QUnit, Selenium for unit, integration, and end-to-end testing

### 4. **Data Storage**
- **Database schema changes** and **SQL scripts** in the `sql` folder
- Stores translation drafts, published translations, and metadata

## System Design
### **Component Interactions**
- The frontend communicates with the backend via API calls (AJAX, REST-like requests)
- Vue components interact with the Vuex store to manage state
- Backend processes requests, interacts with the database, and integrates external services
- Data flow:
  - **Frontend → Backend**: API calls for translation actions
  - **Backend → Database**: Storing and retrieving translation data
  - **Backend → External Services**: Fetching machine translation suggestions

### **Architectural Patterns**
- **Frontend**: Component-based design (Vue.js, Vuex for state management)
- **Backend**: PHP extension model (MediaWiki hooks, service wiring, DTOs)
- **Testing**: Multi-layered approach (unit, integration, end-to-end tests)
- **MVC-like Structure**:
  - Frontend: Views, components, store
  - Backend: API endpoints, service classes, database layer

## System Design Diagram Guidelines
### **Main Sections**
1. **Frontend Client**
   - Detail Vue components, store, and plugins
   - Highlight the specialized `minT` interface
2. **Backend Extension**
   - Show PHP modules, API endpoints, and MediaWiki integration
3. **External Services**
   - Display connections to machine translation services and Chart.js
4. **Data Storage**
   - Represent database schema and its role in storing translations

### **Diagram Elements**
- **Arrows**: Show data flow between frontend, backend, external services, and database
- **Color Coding**:
  - Blue: Vue components
  - Green: Vuex store and routing
  - Orange: PHP backend modules
  - Red: External services
  - Purple: Database
- **Annotations**: Label key concepts like “Component-based UI”, “Service Layer”, “MVC-like Frontend”, etc.

## Conclusion
This README provides an in-depth understanding of the **ContentTranslation** extension’s architecture. By following the outlined system design diagram guidelines, developers can better comprehend how its various components interact, ensuring a well-structured and maintainable codebase.
