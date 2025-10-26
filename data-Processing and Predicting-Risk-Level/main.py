import pandas as pd
import mysql.connector
from mysql.connector import Error
from sklearn.impute import SimpleImputer
from sklearn.ensemble import IsolationForest, RandomForestClassifier
from sklearn.model_selection import train_test_split
from sklearn.metrics import classification_report
import numpy as np

def create_db_connection():
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='college_portal11',
            user='root',  
            password=''   
        )
        return connection
    except Error as e:
        print(f"Error connecting to MySQL: {e}")
        return None

def check_and_create_tables():
    
    """Check if students and staff tables exist, create them if they don't"""
    
    connection = create_db_connection()
    if connection is None:
        return False
    
    try:
        cursor = connection.cursor()
        
        # Check if students table exists
        cursor.execute("SHOW TABLES LIKE 'students'")
        result = cursor.fetchone()
        
        if not result:
            # Create the students table if it doesn't exist
            create_students_table_query = """
            CREATE TABLE students (
                id INT AUTO_INCREMENT PRIMARY KEY,
                student_name VARCHAR(100) NOT NULL,
                reg_no INT NOT NULL UNIQUE,
                staff_name VARCHAR(100),
                marks DECIMAL(5,2),
                attendance DECIMAL(5,2),
                gmail VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                risk_level ENUM('Low','Moderate','High')
            )
            """
            cursor.execute(create_students_table_query)
            print("✅ Created students table")
        
        # Check if staff table exists
        cursor.execute("SHOW TABLES LIKE 'staff'")
        result = cursor.fetchone()
        
        if not result:
            
            # Create the staff table if it doesn't exist
            
            create_staff_table_query = """
            CREATE TABLE staff (
                id INT AUTO_INCREMENT PRIMARY KEY,
                staff_name VARCHAR(100) NOT NULL,
                email VARCHAR(100) NOT NULL UNIQUE,
                password VARCHAR(255) NOT NULL,
                department VARCHAR(100)
            )
            """
            cursor.execute(create_staff_table_query)
            print("✅ Created staff table")
        
        return True
    except Error as e:
        print(f"Error checking/creating tables: {e}")
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def insert_student_data(df):
    
    """Insert student data with risk levels into the database"""
    
    connection = create_db_connection()
    if connection is None:
        return False
    
    try:
        cursor = connection.cursor()
        
        # Clear existing data 
        cursor.execute("DELETE FROM students")
        cursor.execute("ALTER TABLE students AUTO_INCREMENT = 1")
        
        # Insert new data
        for _, row in df.iterrows():
            insert_query = """
                INSERT INTO students 
                (student_name, reg_no, staff_name, marks, attendance, gmail, password, risk_level) 
                VALUES (%s, %s, %s, %s, %s, %s, %s, %s)
            """
            cursor.execute(insert_query, (
                row['Student_Name'], 
                row['Reg_No'], 
                row['Staff_Name'], 
                row['Marks'], 
                row['Attendance'], 
                row['Gmail'], 
                row['Password'],  
                row['Risk_Level']
            ))
        
        connection.commit()
        return True
    except Error as e:
        print(f"Error inserting student data: {e}")
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def insert_staff_data_from_excel():
    
    """Insert staff data from Excel file into the database"""
    
    try:
        df = pd.read_excel('staff_data.xlsx')
        print("✅ Successfully loaded staff_data.xlsx")
        print(f"Found {len(df)} staff records")
    except FileNotFoundError:
        print("❌ Error: staff_data.xlsx file not found")
        return False
    except Exception as e:
        print(f"❌ Error reading staff Excel file: {e}")
        return False
    
    required_columns = ['staff_name', 'email', 'password', 'department']
    for col in required_columns:
        if col not in df.columns:
            print(f"❌ Error: Required column '{col}' not found in staff Excel file")
            print(f"Available columns: {list(df.columns)}")
            return False
    
    connection = create_db_connection()
    if connection is None:
        return False
    
    try:
        cursor = connection.cursor()
        
        # Clear existing data 
        cursor.execute("DELETE FROM staff")
        #cursor.execute("ALTER TABLE staff AUTO_INCREMENT = 1")
        print("🧹 Cleared existing staff data")
        
        # Insert new data
        success_count = 0
        for _, row in df.iterrows():
            try:
                insert_query = """
                    INSERT INTO staff (staff_name, email, password, department) 
                    VALUES (%s, %s, %s, %s)
                """
                cursor.execute(insert_query, (
                    row['staff_name'], 
                    row['email'], 
                    row['password'], 
                    row['department']
                ))
                success_count += 1
            except Error as e:
                print(f"⚠️  Error inserting staff {row['staff_name']}: {e}")
        
        connection.commit()
        print(f"✅ Successfully inserted {success_count} out of {len(df)} staff records")
        return True
        
    except Error as e:
        print(f"❌ Error inserting staff data: {e}")
        return False
    finally:
        if connection.is_connected():
            cursor.close()
            connection.close()

def create_synthetic_training_data():
    
    """Create synthetic training data with clear risk level patterns"""
    
    np.random.seed(42) 
    n_samples = 1000
    data = []
    
    # Low risk: High marks AND high attendance
    n_low = n_samples // 3
    for _ in range(n_low):
        marks = np.random.normal(85, 5)  # High marks
        attendance = np.random.normal(85, 5)  # High attendance
        data.append([marks, attendance, 'Low'])
    
    # Moderate risk: Medium marks OR medium attendance
    n_moderate = n_samples // 3
    for _ in range(n_moderate):
        if np.random.random() > 0.5:
            marks = np.random.normal(65, 5)  # Medium marks
            attendance = np.random.normal(75, 5)  # High attendance
        else:
            marks = np.random.normal(80, 5)  # High marks
            attendance = np.random.normal(70, 5)  # Medium attendance
        data.append([marks, attendance, 'Moderate'])
    
    # High risk: Low marks OR low attendance
    n_high = n_samples // 3
    for _ in range(n_high):
        marks = np.random.normal(45, 10)  # Low marks
        attendance = np.random.normal(55, 10)  # Low attendance
        data.append([marks, attendance, 'High'])

    df = pd.DataFrame(data, columns=['Marks', 'Attendance', 'Risk_Level'])
    df['Marks'] = df['Marks'].clip(0, 100)
    df['Attendance'] = df['Attendance'].clip(0, 100)
    
    return df

def train_risk_classifier():
    
    """Train a machine learning classifier to predict risk levels"""
    
    train_df = create_synthetic_training_data()
    X = train_df[['Marks', 'Attendance']]
    y = train_df['Risk_Level']
    X_train, X_test, y_train, y_test = train_test_split(X, y, test_size=0.2, random_state=42)
    
    # Train Random Forest classifier
    clf = RandomForestClassifier(n_estimators=100, random_state=42)
    clf.fit(X_train, y_train)
    y_pred = clf.predict(X_test)
    print("📊 Model Evaluation:")
    print(classification_report(y_test, y_pred))
    
    return clf

def process_student_data():
    
    """Process student data and predict risk levels"""
    
    try:
        df = pd.read_excel("student_data.xlsx")
        print("✅ Successfully loaded student_data.xlsx")
    except FileNotFoundError:
        print("❌ Error: student_data.xlsx file not found")
        return None
    except Exception as e:
        print(f"❌ Error reading student Excel file: {e}")
        return None
    
    required_columns = ['Student_Name', 'Reg_No', 'Staff_Name', 'Marks', 'Attendance', 'Gmail', 'Password']
    for col in required_columns:
        if col not in df.columns:
            print(f"❌ Error: Required column '{col}' not found in student Excel file")
            print(f"Available columns: {list(df.columns)}")
            return None
    
    # ---------------- Data Cleaning ----------------
    print("🔄 Processing student data...")
    
    # Missing data
    imputer = SimpleImputer(strategy="mean")
    df[['Marks', 'Attendance']] = imputer.fit_transform(df[['Marks', 'Attendance']])

    # Removing outliers
    iso = IsolationForest(contamination=0.1, random_state=42)
    outlier_labels = iso.fit_predict(df[['Marks', 'Attendance']])
    df = df[outlier_labels == 1]   
    print(f"📊 After outlier removal: {len(df)} students")
    
    # ---------------- Risk Prediction ----------------
    print("🔮 Training ML model and predicting risk levels...")
    
    clf = train_risk_classifier()
    X = df[['Marks', 'Attendance']]
    df['Risk_Level'] = clf.predict(X)
    
    """
    #Count risk levels
    
    risk_counts = df['Risk_Level'].value_counts()  
    print("📈 Risk Level Distribution:")
    for level, count in risk_counts.items():
        print(f"   {level}: {count} students")
    
        
    print("\n👁️  Sample risk assessments for verification:")
    sample_size = min(5, len(df))
    for i in range(sample_size):
        student = df.iloc[i]
        print(f"   {student['Student_Name']}: Marks={student['Marks']:.1f}, Attendance={student['Attendance']:.1f} → {student['Risk_Level']} risk")
    """
    output_df = df.copy()
    output_df.to_excel("student_risk_output.xlsx", index=False)
    print("✅ Cleaned data with predicted risk levels saved to student_risk_output.xlsx")
    
    return df

def main():
    print("Starting data processing and database insertion...")
    
    # Check and create tables if needed
    if not check_and_create_tables():
        print("❌ Failed to setup database tables")
        return
    
    # inserting the student data 
    student_df = process_student_data()
    if student_df is not None:
        if insert_student_data(student_df):
            print("✅ Student data successfully stored in MySQL database")
        else:
            print("❌ Failed to store student data in MySQL database")
    else:
        print("❌ Student data processing failed")
    
    # Inserting the staff data
    if insert_staff_data_from_excel():
        print("✅ Staff data successfully stored in MySQL database")
    else:
        print("❌ Failed to store staff data in MySQL database")
    
    print("🎉 All operations completed!")

if __name__ == "__main__":
    main()