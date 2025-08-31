# 🎓 Student Mental Health Early Detection AI  

[![Python](https://img.shields.io/badge/Python-3.9+-blue?logo=python)]()  [![PHP](https://img.shields.io/badge/PHP-Backend-purple?logo=php)]()  [![MySQL](https://img.shields.io/badge/MySQL-Database-orange?logo=mysql)]() 
[![Frontend](https://img.shields.io/badge/Frontend-HTML%20%7C%20CSS%20%7C%20Bootstrap-green?logo=html5)]()  

---

## 📖 Overview  

**StudentMH-EarlyDetect-AI** is an **AI-powered system** that predicts student mental health risk levels using **academic performance** and **behavioral data** (focus on **attendance percentage**).  

It leverages a **Machine Learning pipeline** with **Random Forest classification** to accurately detect **Low, Moderate, and High-risk students** and generates **personalized AI-powered study plans** to improve academic performance.  

✨ The platform includes **three login portals**:  
- 👩‍🎓 **Student** → View progress & AI study planner  
- 👨‍🏫 **Staff** → Track students & get high-risk alerts  
- 👩‍💼 **Admin** → Manage data & analytics  

---

## 🎯 Project Objectives  

✔️ Early detection of **mental health issues** through data analysis  
✔️ Identify **at-risk students** for academic failure  
✔️ Break the cycle of **pressure → poor performance → increased pressure**  
✔️ Provide **AI-generated study plans** for students  
✔️ Enable **staff alerts** for high-risk students  

---

## 🛠️ Technical Architecture  

### 🔹 System Components  
- **Machine Learning Pipeline**  
  - 🧩 `SimpleImputer`: Handles missing values  
  - 🚫 `IsolationForest`: Detects anomalies  
  - 🤖 `RandomForestClassifier`: Classifies into **Low / Moderate / High risk**  
  - 📊 Synthetic Data Generation: Creates training data with clear risk patterns

- **Database**: MySQL (student data + predictions)  
- **Backend**: PHP (server-side processing + DB interaction)  
- **Frontend**: HTML, CSS, JavaScript, Bootstrap (3-tier portals)  

---

## 💻 Technology Stack  

### 🛠️ Development Stack
| Layer | Technologies |
|:------|:------------|
| **Frontend** | <img src="https://img.shields.io/badge/HTML5-E34F26?style=flat&logo=html5&logoColor=white" alt="HTML5" height="24"> <img src="https://img.shields.io/badge/CSS3-1572B6?style=flat&logo=css3&logoColor=white" alt="CSS3" height="24"> <img src="https://img.shields.io/badge/JavaScript-F7DF1E?style=flat&logo=javascript&logoColor=black" alt="JavaScript" height="24"> <img src="https://img.shields.io/badge/Bootstrap-563D7C?style=flat&logo=bootstrap&logoColor=white" alt="Bootstrap" height="24"> |
| **Backend** | <img src="https://img.shields.io/badge/PHP-777BB4?style=flat&logo=php&logoColor=white" alt="PHP" height="24"> |
| **Database** | <img src="https://img.shields.io/badge/MySQL-005C84?style=flat&logo=mysql&logoColor=white" alt="MySQL" height="24"> |

### 🤖 Machine Learning Stack
| Component | Technologies |
|:----------|:------------|
| **Language** | <img src="https://img.shields.io/badge/Python-3776AB?style=flat&logo=python&logoColor=white" alt="Python" height="24"> |
| **ML Framework** | <img src="https://img.shields.io/badge/scikit--learn-F7931E?style=flat&logo=scikit-learn&logoColor=white" alt="Scikit-learn" height="24"> |
| **ML Models** | <img src="https://img.shields.io/badge/Random_Forest-00A86B?style=flat&logo=randomforest&logoColor=white" alt="Random Forest" height="24"> <img src="https://img.shields.io/badge/Isolation_Forest-FF6B6B?style=flat&logo=tree&logoColor=white" alt="Isolation Forest" height="24"> <img src="https://img.shields.io/badge/Data_Imputation-9B59B6?style=flat&logo=data&logoColor=white" alt="Data Imputation" height="24"> |

--- 
## 👥 User Roles & Features  

### 👩‍🎓 **Student Login**  
- View personal details & academic performance  
- Access **AI-generated study planner** 📅  
- Monitor personal **risk level** & progress 

![login Screenshot](Screenshots/Screenshot%202025-08-27%20232646.png)

![login dashboard](Screenshots/Screenshot%202025-08-28%20231137.png)

![login dashboard](Screenshots/screencapture-localhost-AiEarlyDetectionStudent-study-plan-php-2025-08-31-13_29_58.png)

### 👨‍🏫 **Staff Login**  
- View **student details & performance**  
- See **accurate risk predictions** (Low / Moderate / High)  
- Get **alerts for high-risk students** 🚨   

![login dashboard](Screenshots/Screenshot%202025-08-31%20133425.png)

### 👩‍💼 **Admin Login**  
- Full **system-wide view** of data & performance  

![login dashboard](Screenshots/Screenshot%202025-08-31%20133552.png)

---

## 🔮 Future Enhancements  

- Enhancing a more **adaptable AI-based study planner**  
- Integration of **additional data sources**   
- Advanced ML models (**Neural Networks, Gradient Boosting**)  
- Real-time **notifications** for urgent risk cases  
- Integration with **counseling services** 🧑‍⚕️  
- **NLP** for analyzing student feedback  

---

## ⚙️ Installation & Setup  

Follow these steps to set up the project on your local machine:  

1️⃣ **Fork or Download the Repository**  
   - Place the project folder inside your `xampp/htdocs/` directory.  

2️⃣ **Start XAMPP Control Panel**  
   - Run **Apache** and **MySQL** services.  

3️⃣ **Create the Database**  
   - Open **phpMyAdmin** or MySQL and create a new database.  
   - Inside this database, create the **Admin table** using the following query:  

   ```sql
   CREATE TABLE admin (
       id INT AUTO_INCREMENT PRIMARY KEY,
       admin_name VARCHAR(100) NOT NULL,
       email VARCHAR(100) NOT NULL UNIQUE,
       password VARCHAR(255) NOT NULL
   );
   ```

  - Insert at least one admin record (you can modify values as needed):
```
INSERT INTO admin (admin_name, email, password) 
VALUES ('System Admin', 'admin@gmail.com', 'admin123');
```


⚠️ Note: Update the database name in your project files.

4️⃣ **Prepare Student & Staff Data (Excel)**  
   - Before running the Python script, create and store the data in Excel format.  
   - **Student Data (student_data.xlsx)** must have the following columns:  
     - `student_name`, `reg_no`, `staff_name`, `marks`, `attendance`, `gmail`, `password`  
   - **Staff Data (staff_data.xlsx)** must have the following columns:  
     - `staff_name`, `email`, `password`, `department`  

   📌 *Refer to the provided files* → `student_data.xlsx` and `staff_data.xlsx` for the correct format.  




 5️⃣ Run the Machine Learning Script

This will read student & staff Excel data, process it, and store results in the database.
```
python main.py
```
6️⃣ Run the PHP Application

- Open your browser and navigate to the project folder inside `htdocs`.
```
http://localhost/StudentMH-EarlyDetect-AI/login.php

```

- Use the Admin / Staff / Student login credentials to access the system.

---


## 🤝 Contributing  

Collaboration makes everything better 🌟  
- Found a bug? 🐞 Fix it or open an issue!  
- Got an idea? 💡 Share it! 
- Want to improve? ✨ Go for it!  

Fork → Code → PR = 🎉  

   