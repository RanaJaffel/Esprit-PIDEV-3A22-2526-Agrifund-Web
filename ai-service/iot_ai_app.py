from fastapi import FastAPI
from pydantic import BaseModel
from typing import List, Optional
import numpy as np
from sklearn.ensemble import IsolationForest

app = FastAPI(title="AgriFund Module4 IoT AI")

class Measure(BaseModel):
    value: float

class DetectRequest(BaseModel):
    projectId: int
    sensorId: int
    typeMesure: str
    history: List[Measure]
    currentValue: float
    contamination: Optional[float] = 0.05

class DetectResponse(BaseModel):
    isAnomaly: bool
    anomalyScore: float
    severity: str
    reason: str

@app.get("/health")
def health():
    return {"status": "ok"}

@app.post("/detect-anomaly", response_model=DetectResponse)
def detect(req: DetectRequest):

    if len(req.history) < 20:
        return DetectResponse(
            isAnomaly=False,
            anomalyScore=0.0,
            severity="INFO",
            reason="Not enough history"
        )

    X = np.array([[m.value] for m in req.history])

    model = IsolationForest(
        n_estimators=150,
        contamination=req.contamination,
        random_state=42
    )
    model.fit(X)

    score = float(model.decision_function([[req.currentValue]])[0])
    pred = int(model.predict([[req.currentValue]])[0])

    is_anomaly = (pred == -1)

    if is_anomaly:
        severity = "CRITICAL" if score < -0.2 else "WARN"
    else:
        severity = "INFO"

    return DetectResponse(
        isAnomaly=is_anomaly,
        anomalyScore=round(score, 4),
        severity=severity,
        reason="IsolationForest anomaly" if is_anomaly else "Normal"
    )