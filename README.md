# ✅  Uruchomienie aplikacji w Dockerze
## Aby zbudować obrazy i uruchomić kontenery, użyj następujących komend:
```
docker compose build
docker compose up
```
## Aplikacja będzie dostępna pod adresem:
👉 http://localhost:8080

# 📝 Przykładowe zapytania API
## 🔹 1️⃣ Rejestracja pracownika
### 🛠 Endpoint:
```
POST http://localhost:8080/api/employee
```
📌 Body:
```
{
    "name": "Bartek",
    "surname": "Sosin"
}
```

## 🔹 2️⃣ Rejestracja czasu pracy
### 🛠 Endpoint:
```
POST http://localhost:8080/api/worktime
```
📌 Body:
```
{
    "employee_id": "d154fe0f-248f-4e81-8a40-e697d3ed8daa",
    "start_time": "06.06.2025 08:00",
    "end_time": "06.06.2025 18:00"
}
```

## 🔹 3️⃣ Podsumowanie dnia
### 🛠 Endpoint:
```
POST http://localhost:8080/api/employee/work-summary/daily
```
📌 Body:
```
{
    "employee_id": "d154fe0f-248f-4e81-8a40-e697d3ed8daa",
    "date": "06.06.2025"
}
```

## 🔹 4️⃣ Podsumowanie miesiąca
### 🛠 Endpoint:
```
POST http://localhost:8080/api/employee/work-summary/monthly
```
📌 Body:
```
{
    "employee_id": "d154fe0f-248f-4e81-8a40-e697d3ed8daa",
    "date": "04.2025"
}
```
