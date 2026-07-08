# SimpleSearch System Diagrams

The following diagrams describe the current SimpleSearch architecture and workflows.

## 1) Flow Chart — Query to Results

```mermaid
flowchart TD
    A[User enters query] --> B{Query empty?}
    B -- Yes --> C[Render home page]
    B -- No --> D[PublicController::index]
    D --> E[SearchService::search]
    E --> F[Log query in query_log]
    F --> G{FTS data available?}
    G -- Yes --> H[Build FTS query and execute]
    H --> I{Results found?}
    G -- No --> J[Fallback LIKE search]
    I -- Yes --> K[Paginate results]
    I -- No --> J
    J --> L{Results found?}
    L -- Yes --> K
    L -- No --> M[Generate did-you-mean from dictionary]
    M --> K
    K --> N[Render search results page]
```

## 2) Block Diagram — Layered Architecture

```mermaid
flowchart TB
    subgraph Presentation Layer
        P1[Public Templates]
        P2[Admin Templates]
        P3[Controllers<br/>PublicController / AdminController / AuthController]
        P4[Middleware<br/>Auth / CSRF / Session]
    end

    subgraph Application Layer
        A1[SearchService]
        A2[CrawlerService]
        A3[DictionaryService]
        A4[ImportExportService]
    end

    subgraph Service & Repository Layer
        R1[SiteRepository]
        R2[QueryLogRepository]
        R3[CrawlLogRepository]
        R4[DictionaryRepository]
        R5[MenuRepository]
        R6[PageRepository]
        R7[SettingsRepository]
    end

    subgraph Data Layer
        D1[(sites)]
        D2[(sites_fts)]
        D3[(query_log)]
        D4[(crawl_log)]
        D5[(dictionary)]
        D6[(menu_items)]
        D7[(custom_pages)]
        D8[(site_settings)]
    end

    P1 --> P3
    P2 --> P3
    P4 --> P3

    P3 --> A1
    P3 --> A2
    P3 --> A3
    P3 --> A4

    A1 --> R1
    A1 --> R2
    A1 --> R4
    A2 --> R1
    A2 --> R3
    A2 --> R4
    A3 --> R4
    A4 --> R1
    A4 --> R4

    R1 --> D1
    R1 --> D2
    R2 --> D3
    R3 --> D4
    R4 --> D5
    R5 --> D6
    R6 --> D7
    R7 --> D8
```

## 3) DFD Level 0 — Context Diagram

```mermaid
flowchart LR
    U[Public User]
    A[Admin User]
    W[External Websites / Sitemaps]

    S((SimpleSearch System))

    U -->|Search query / Suggest request| S
    S -->|Search results / Suggestions / Public pages| U

    A -->|Manage sites, crawl, pages, menu, settings| S
    S -->|Dashboard, logs, import/export, status| A

    S -->|HTTP fetch requests| W
    W -->|HTML/XML content| S
```

## 4) DFD Level 1 — Major Processes

```mermaid
flowchart LR
    U[Public User]
    A[Admin User]
    W[External Websites]

    P1((1.0 Search))
    P2((2.0 Crawl))
    P3((3.0 Suggest))
    P4((4.0 Manage Content))
    P5((5.0 Log & Analytics))

    DS1[(Sites + FTS)]
    DS2[(Dictionary)]
    DS3[(Query Log)]
    DS4[(Crawl Log)]
    DS5[(Pages/Menu/Settings)]

    U -->|Search terms| P1
    P1 -->|Results| U
    P1 <--> DS1
    P1 --> DS3
    P1 <--> DS2

    U -->|Prefix text| P3
    P3 -->|Suggestions| U
    P3 <--> DS3

    A -->|Crawl URL / sitemap| P2
    P2 -->|Crawl status| A
    P2 -->|Fetch page data| W
    W -->|Page content| P2
    P2 --> DS1
    P2 --> DS2
    P2 --> DS4

    A -->|CRUD requests| P4
    P4 -->|Updated content| A
    P4 <--> DS1
    P4 <--> DS5

    A -->|Dashboard request| P5
    P5 -->|Counts/recent logs| A
    P5 <--> DS3
    P5 <--> DS4
    P5 <--> DS1
```

## 5) DFD Level 2 — Decomposition of Search Process (1.0)

```mermaid
flowchart TD
    U[Public User] --> S1[1.1 Capture query and page]
    S1 --> S2{Query provided?}
    S2 -- No --> S9[1.9 Return empty/home response]
    S2 -- Yes --> S3[1.2 Normalize query]

    S3 --> S4[1.3 Log query]
    S4 --> QL[(query_log)]

    S4 --> S5{FTS ready?}
    S5 -- Yes --> S6[1.4 Build FTS terms]
    S6 --> S7[1.5 Run FTS search + count]
    S7 --> SF[(sites_fts / sites)]
    S7 --> S8{Results found?}

    S5 -- No --> S10[1.6 Run LIKE search + count]
    S8 -- No --> S10
    S10 --> ST[(sites)]

    S8 -- Yes --> S11[1.7 Paginate and format results]
    S10 --> S12{Results found?}
    S12 -- Yes --> S11
    S12 -- No --> S13[1.8 Build did-you-mean]
    S13 --> D[(dictionary)]
    S13 --> S11

    S11 --> S14[1.10 Return response payload]
    S9 --> S14
    S14 --> U
```

## 6) ER Diagram — Database Schema

```mermaid
erDiagram
    sites {
        int id PK
        string url UK
        string title
        string description
        text content
        datetime crawled_at
    }

    sites_fts {
        int rowid PK
        string url
        string title
        string description
        text content
    }

    dictionary {
        string word PK
    }

    query_log {
        int id PK
        string query
        datetime searched_at
    }

    crawl_log {
        int id PK
        string url
        string status
        string message
        datetime crawled_at
    }

    menu_items {
        int id PK
        string label
        string url
        string icon
        int sort_order
        bool is_active
        bool open_new_tab
        datetime created_at
    }

    custom_pages {
        int id PK
        string slug UK
        string title
        text content
        bool is_published
        datetime created_at
        datetime updated_at
    }

    site_settings {
        string key PK
        string value
        datetime updated_at
    }

    sites ||--|| sites_fts : "indexed by FTS triggers"
```

## 7) Activity Diagram — User Search Flow

```mermaid
flowchart TD
    A([Start]) --> B[Open search page]
    B --> C[Type query]
    C --> D{Need suggestions?}
    D -- Yes --> E[AJAX suggest request]
    E --> F[Display suggestion list]
    F --> G[Select suggestion or continue typing]
    G --> H[Submit search]
    D -- No --> H

    H --> I[System validates and trims query]
    I --> J{Query empty?}
    J -- Yes --> K[Show home state]
    J -- No --> L[Run search and log query]

    L --> M{Results found?}
    M -- Yes --> N[Show paginated results]
    M -- No --> O[Show no-results + did-you-mean]

    N --> P{Open result or change page?}
    P -- Open result --> Q[Visit target URL]
    P -- Change page --> N
    P -- New query --> C

    O --> R{Try suggested query?}
    R -- Yes --> H
    R -- No --> C

    K --> C
    Q --> A
```
