import pandas as pd
from sklearn.impute import SimpleImputer
from sklearn.preprocessing import StandardScaler
from sklearn.ensemble import IsolationForest
from sklearn.cluster import KMeans

df = pd.read_excel("student_data.xlsx")

# ---------------- Data Cleaning ----------------

# Missing data
imputer = SimpleImputer(strategy="mean")
df[['Marks', 'Attendance']] = imputer.fit_transform(df[['Marks', 'Attendance']])

# Removing outliers
iso = IsolationForest(contamination=0.1, random_state=42)
outlier_labels = iso.fit_predict(df[['Marks', 'Attendance']])
df = df[outlier_labels == 1]   

# Scale data
scaler = StandardScaler()
scaled_features = scaler.fit_transform(df[['Marks', 'Attendance']])

# ---------------- Risk Prediction ----------------

kmeans = KMeans(n_clusters=3, random_state=42, n_init=10)
df['Cluster'] = kmeans.fit_predict(scaled_features)

cluster_means = df.groupby('Cluster')[['Marks', 'Attendance']].mean()
risk_mapping = {
    cluster_means.sort_values(by=['Marks', 'Attendance']).index[0]: 'High',
    cluster_means.sort_values(by=['Marks', 'Attendance']).index[1]: 'Moderate',
    cluster_means.sort_values(by=['Marks', 'Attendance']).index[2]: 'Low'
}
df['Risk_Level'] = df['Cluster'].map(risk_mapping)

# Saving the risk prediction result in the saperate file 

df.drop(columns=['Cluster'], inplace=True)
df.to_excel("student_risk_output.xlsx", index=False)

print("✅ Cleaned data with predicted risk levels saved to student_risk_output.xlsx")