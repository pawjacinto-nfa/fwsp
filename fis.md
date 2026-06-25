# Project Requirements

## Project Information

**Project Name:**
Farmer's Who Sold Palay to NFA

**Version:**
v1.0

**Developer(s):**
Paw Jacinto
Rain dela Cruz
Boots Torres
Emman Gormise

**Date Created:**
June 11, 2026


---

# Functional Requirements

This system records farmer details and warehouse transactions, Palay sold to NFA(National Food Authority) and is able to produce reports on daily to annual basis.
Reports varies from the following:
1. Sex Disaggregated Data
2. Farmers List (Regional to Warehouse selections)
3. Total Delivery per day or per month or per quarter or per year


## User Authentication

* Warehouse Managers must be able to log in using a username and password.
* They must be able to log out securely.
* Passwords must be encrypted.

## Dashboard

* Display analytics on farmer demographics.
* Display choices between encoding options. (Farmer Information or Warehouse Transaction)
* Display recent activities.

## Document Management

* Able to produce pdf reports viewable in a new tab or an iframe.
* Upload attachments such as photo of the farmer to be encoded.


## Notifications

* Notify warehouse managers of new records uploaded by warehouse assistants.


# User Interface Requirements

## Design Principles
- Clean and modern interface
- Responsive design for desktop and mobile devices
- Consistent navigation across all pages
- Easy-to-read typography
- Accessible color contrast

## Theme Preferences
- Primary Color: Green
- Secondary Color: Mustard
- Font Family: Poppins
- Light/Dark Mode Support: Yes/No
- Background should be in green to white monochrome
- All loading screens must show an animated Palay icon spinning slowly
- All forms in logical order must have a progress bar indicating the progress of the form, with color transition from yellow to green, showing a check on the end.
- Homepage must be the Dashboard buttons, not the login page.
- Login Page and Registration must be in modal form: Background Dashboard will be transparent in this form.  

## Layout Requirements
- Dashboard should display key metrics at a glance
- Navigation menu should remain visible on all pages
- Forms should be grouped logically
- Action buttons should be clearly labeled

## User Experience Requirements
- Maximum of 3 clicks to reach common functions
- Confirmation dialogs for delete actions
- Loading indicators for long-running processes
- Error messages should be user-friendly

## Dashboard Preferences
- Show recent activities
- Quick access buttons for common actions

## Accessibility Requirements
- Keyboard navigation support
- Responsive design for various screen sizes
- Readable font sizes
- High contrast option (optional)
---

# User Roles

| Role            	| Description                   |
| --------------- 	| ----------------------------- |
| Administrator   	| Full system access            |
| Managers	  	| Monitor transactions and records |
| Warehouse Managers    | Create and process farmer profiles and transactions  |
| Viewer          	| Read-only access              |

---

# Non-Functional Requirements

## Performance

* System should load pages within 3 seconds.
* Support at least 100 concurrent users.

## Security

* Encrypted passwords.
* Session timeout after inactivity.
* Role-based access control.

## Availability

* System should be available 24/7.
* Daily database backups.

---

# Hardware Requirements

## Server

* CPU: At least 4 core processors
* RAM: 4 GIG 
* Storage: [Specify]

## Client

Modern web browser
Internet connection

---

# Software Requirements

## Development Tools

PHP
MySQL
Visual Studio Code
GitHub

---

# Database Requirements

### Tables

Users
Location Library for Region (Regions 1-15), Branch Offices, and Warehouse Offices per Branch
Audit and Logs
Notifications
-Farmer Details (RSBSA Number, First Name, Middle Name, Last Name, Full Home Address, BirthDate, Birthplace, Civil Status, Name of Spouse, No. of Household Members/Dependents, Contact Number, Email Address, Sex, Gender Orientation in checkbox[Lesbian, Gay, Bisexual, Transgender, Other 'specify'], Sector [Persons with Disability, Indigenous People, Senior Citizen, Muslim, Youth, Adult)
-Farmers Landholding Data (Classification or Status of Landholding in checkbox [Riceland, Cornland, Owner-Tiller, Landowner/lessor, CLT Holder/recipient, Irrigated [Yes/No], Harvest Sharing [Percentage for Lessor and Lessee], Palay Location, Estimated Palay Harvested Area in hectares, Estimated Average Yield per hectare, Farmer Organization)
-Transaction Details for Individual Farmer-Seller: For Individual Farmers Selling (In-Warehouse or Mobile Procurement, Farmer RSBSA Number [Based on Inputted Farmers from the Farmers Database], Verified Farm Area in hectars, Date of Delivery, Warehouse Stock Receipt Number, Price per Kilogram, Net Kilogram, Number of Bags Delivered in 50kg)
-Transaction Details For Farmers Organizations Selling (In-Warehouse or Mobile, Name of Farmer Organization, Full Name of Authorized Representative, Total Number of Farmer-Members, Names of Farmers[must be searched and selected from pool of farmers already in the database - referenced by name of farmer organization]
-Farmers Organization (Name of FO, Names of Farmers Registered Under the FO, Names of Farmers under this FO)

---

# Dependencies

PHP 8.2+
MySQL 8.0+
Apache/Nginx
Bootstrap 5
jQuery
CSS customizable

---

# Installation Requirements

1. Install web server.
2. Install database server.
3. Import database schema.
4. Configure environment variables.
5. Start application.

---

# Assumptions and Constraints

## Assumptions

-Users have internet access.
- Users have valid accounts.

## Constraints

- Limited to authorized personnel.
- Requires modern web browser support.

---

# Future Enhancements

- Email notifications
- Mobile application
- Digital signatures
- Advanced analytics dashboard

---

# Revision History

| Version | Date   | Description     | Author |
| ------- | ------ | --------------- | ------ |
| 1.0     | [Date] | Initial Release | [Name] |
