# Arcturus Katalog Admin-Tool
A modern, visual Admin Tool for managing Arcturus Emulator Catalogs.
# 👑 Xalo Habbo Catalog Platinum

**A modern, visual Admin Tool for managing Habbo Hotel Catalogs.** *Compatible with Arcturus & Morningstar database structures.*

Stop editing your catalog in slow database tables. **Habbo Catalog Manager** offers a fully visual interface with **Drag & Drop sorting**, live image previews, and cross-tab item moving.

![Screenshot Preview]([https://prnt.sc/xypZc884EGSu](https://prnt.sc/xypZc884EGSu))
*(Add a screenshot of your tool here later)*

## 🚀 Xalo Features

* **⚡ Drag & Drop Sorting:** Reorder items and categories instantly with your mouse.
* **📂 Cross-Tab Moving:** Drag categories from sub-folders directly into other main tabs easily.
* **🌍 Multi-Language System:** Fully localized for **DE, EN, ES, BR, TR, KRD** (switchable in config).
* **👁️ Live Previews:** See Header, Teaser, and Icon images immediately while typing.
* **🔍 Live Search:** Filter through thousands of catalog pages in milliseconds.
* **📝 Hybrid Layout Editor:** Select from standard layouts or type custom layout names (e.g., `info_loyalty`).
* **⚙️ Easy Configuration:** Clean separation of code and database credentials.

## 📋 Requirements

* PHP 7.4 or higher
* MySQL / MariaDB Database
* A Habbo Retro Database (Arcturus / Morningstar structure)
* Webserver (Apache/Nginx/IIS)

## 🛠️ Installation

1.  **Clone or Download:**
    Upload all files to your webserver (e.g., `/admin/catalog`).

2.  **Configuration:**
    * Rename `config.sample.php` to `config.php`.
    * Open `config.php` and enter your **MySQL Database credentials**.
    * Set the correct paths to your **SWF images** (`$url_images` and `$url_icons`).
    * Choose your language (e.g., `$language = 'en';`).

3.  **Run:**
    Navigate to the folder in your browser (e.g., `yourhotel.com/admin/catalog`).

## 🎮 How to Use

* **Editing:** Click on any category name in the sidebar to load the editor on the right.
* **Sorting:**
    1.  Click the **"SORT / MOVE"** button at the top.
    2.  All folders will auto-expand.
    3.  Drag items to reorder them within a list.
    4.  To move an item to a different main tab, drag it onto the tab name at the top.
* **Saving:** Text changes require clicking "SAVE". Sorting is saved **automatically** instantly.

## 📂 File Structure

* `index.php` - Main Interface & Frontend Logic.
* `api.php` - Backend Handler (AJAX requests for saving/loading).
* `db.php` - PDO Database Connection loader.
* `languages.php` - Translation strings.
* `config.php` - **(Ignored by Git)** Your private settings.

## 🤝 Credits

* **Backend & Logic:** Custom PHP + a likkle work from Gemini
* **Frontend Libraries:** * [Nestable2](https://github.com/kemalhalic/Nestable2) (Tree sorting)
    * [SortableJS](https://github.com/SortableJS/Sortable) (Tab sorting)
    * [jQuery](https://jquery.com/)
* **Icons:** [FontAwesome](https://fontawesome.com/)

---
*Made for the Habbo Community. from xKiwi*
