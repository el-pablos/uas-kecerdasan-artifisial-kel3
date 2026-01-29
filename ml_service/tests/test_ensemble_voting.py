"""
========================================
UNIT TESTS - ENSEMBLE VOTING CLASSIFIER
PyTest untuk validasi modul Ensemble Voting
========================================

Lead Researcher & Developer:
  MUHAMMAD AKBAR HADI PRATAMA (@el-pablos)
"""

import pytest
import numpy as np
import sys
import os

# Add parent directory to path
sys.path.insert(0, os.path.dirname(os.path.dirname(os.path.abspath(__file__))))

from ensemble_voting import (
    EnsembleVotingClassifier,
    create_ensemble_classifier,
    ThreatLevel,
    ModelPrediction,
    EnsembleResult
)


class TestEnsembleVotingClassifier:
    """Test suite untuk EnsembleVotingClassifier."""
    
    @pytest.fixture
    def trained_ensemble(self):
        """Fixture untuk membuat dan melatih ensemble."""
        np.random.seed(42)
        # Generate normal data untuk training (reduced for speed)
        X_train = np.random.randn(50, 6) * 0.5 + 5
        
        ensemble = EnsembleVotingClassifier(contamination=0.1, random_state=42)
        ensemble.fit(X_train)
        
        return ensemble
    
    @pytest.fixture
    def untrained_ensemble(self):
        """Fixture untuk ensemble yang belum dilatih."""
        return EnsembleVotingClassifier(contamination=0.1, random_state=42)
    
    def test_initialization(self, untrained_ensemble):
        """Test inisialisasi ensemble."""
        assert untrained_ensemble is not None
        assert len(untrained_ensemble.models) == 3
        assert 'isolation_forest' in untrained_ensemble.models
        assert 'ocsvm' in untrained_ensemble.models
        assert 'lof' in untrained_ensemble.models
        assert untrained_ensemble.is_fitted == False
    
    def test_fit(self, untrained_ensemble):
        """Test training ensemble."""
        np.random.seed(42)
        X_train = np.random.randn(100, 6)
        
        untrained_ensemble.fit(X_train)
        
        assert untrained_ensemble.is_fitted == True
    
    def test_predict_not_fitted_raises_error(self, untrained_ensemble):
        """Test prediksi tanpa training harus error."""
        X_test = np.array([[1, 2, 3, 4, 5, 6]])
        
        with pytest.raises(RuntimeError):
            untrained_ensemble.predict(X_test)
    
    def test_predict_normal_sample(self, trained_ensemble):
        """Test prediksi sample normal."""
        # Sample yang mirip dengan training data (normal)
        X_normal = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        result = trained_ensemble.predict(X_normal)
        
        assert isinstance(result, EnsembleResult)
        assert result.threat_level in [ThreatLevel.NORMAL, ThreatLevel.SUSPICIOUS]
        assert 0 <= result.consensus_score <= 1
    
    def test_predict_anomaly_sample(self, trained_ensemble):
        """Test prediksi sample anomaly (outlier)."""
        # Sample yang sangat berbeda dari training data
        X_anomaly = np.array([[100, -50, 200, 1000, 500, 50]])
        
        result = trained_ensemble.predict(X_anomaly)
        
        assert isinstance(result, EnsembleResult)
        # Minimal satu model harus mendeteksi anomaly
        assert result.threat_level in [ThreatLevel.SUSPICIOUS, ThreatLevel.HIGH, ThreatLevel.CRITICAL]
        assert result.consensus_score > 0
    
    def test_threat_level_normal(self, trained_ensemble):
        """Test threat level NORMAL (0 vote anomaly)."""
        # Cari sample yang sangat normal
        X_very_normal = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        result = trained_ensemble.predict(X_very_normal)
        
        # Jika semua vote normal
        if sum(result.voting_breakdown.values()) == 0:
            assert result.threat_level == ThreatLevel.NORMAL
    
    def test_voting_breakdown_structure(self, trained_ensemble):
        """Test struktur voting breakdown."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        result = trained_ensemble.predict(X_test)
        
        assert 'isolation_forest' in result.voting_breakdown
        assert 'ocsvm' in result.voting_breakdown
        assert 'lof' in result.voting_breakdown
        
        # Semua nilai harus boolean
        for model, voted in result.voting_breakdown.items():
            assert isinstance(voted, bool)
    
    def test_individual_predictions(self, trained_ensemble):
        """Test individual predictions dari setiap model."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        result = trained_ensemble.predict(X_test)
        
        assert len(result.individual_predictions) == 3
        
        for pred in result.individual_predictions:
            assert isinstance(pred, ModelPrediction)
            assert pred.prediction in [1, -1]
            assert 0 <= pred.confidence <= 1
    
    def test_consensus_score_range(self, trained_ensemble):
        """Test bahwa consensus score dalam range valid."""
        samples = [
            np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]]),  # Normal
            np.array([[100, 100, 100, 100, 100, 100]]),  # Anomaly
            np.array([[0, 0, 0, 0, 0, 0]]),  # Edge case
        ]
        
        for X in samples:
            result = trained_ensemble.predict(X)
            assert 0 <= result.consensus_score <= 1
    
    def test_explanation_not_empty(self, trained_ensemble):
        """Test bahwa explanation tidak kosong."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        result = trained_ensemble.predict(X_test)
        
        assert result.explanation is not None
        assert len(result.explanation) > 0
    
    def test_predict_proba(self, trained_ensemble):
        """Test probability-like output."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        proba = trained_ensemble.predict_proba(X_test)
        
        assert 'normal' in proba
        assert 'anomaly' in proba
        assert 'threat_level' in proba
        
        # Probabilitas harus sum ke 1
        assert abs(proba['normal'] + proba['anomaly'] - 1.0) < 0.01
    
    def test_get_model_info(self, trained_ensemble):
        """Test informasi model."""
        info = trained_ensemble.get_model_info()
        
        assert info['ensemble_type'] == 'Voting Classifier'
        assert info['n_models'] == 3
        assert info['is_fitted'] == True
        assert 'isolation_forest' in info['models']
        assert 'ocsvm' in info['models']
        assert 'lof' in info['models']
    
    def test_predict_single_model(self, trained_ensemble):
        """Test prediksi dari satu model saja."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        pred_if = trained_ensemble.predict_single_model('isolation_forest', X_test)
        pred_ocsvm = trained_ensemble.predict_single_model('ocsvm', X_test)
        pred_lof = trained_ensemble.predict_single_model('lof', X_test)
        
        assert pred_if.model_name == 'isolation_forest'
        assert pred_ocsvm.model_name == 'ocsvm'
        assert pred_lof.model_name == 'lof'
    
    def test_predict_single_model_unknown(self, trained_ensemble):
        """Test prediksi dengan nama model yang tidak dikenal."""
        X_test = np.array([[5.0, 5.0, 5.0, 5.0, 5.0, 5.0]])
        
        with pytest.raises(ValueError):
            trained_ensemble.predict_single_model('unknown_model', X_test)


class TestThreatLevelLogic:
    """Test threat level determination logic."""
    
    def test_determine_threat_level_normal(self):
        """Test threat level dengan 0 anomaly votes."""
        ensemble = EnsembleVotingClassifier()
        level = ensemble._determine_threat_level(0, 3)
        assert level == ThreatLevel.NORMAL
    
    def test_determine_threat_level_suspicious(self):
        """Test threat level dengan 1 anomaly vote."""
        ensemble = EnsembleVotingClassifier()
        level = ensemble._determine_threat_level(1, 3)
        assert level == ThreatLevel.SUSPICIOUS
    
    def test_determine_threat_level_high(self):
        """Test threat level dengan 2 anomaly votes."""
        ensemble = EnsembleVotingClassifier()
        level = ensemble._determine_threat_level(2, 3)
        assert level == ThreatLevel.HIGH
    
    def test_determine_threat_level_critical(self):
        """Test threat level dengan 3 anomaly votes."""
        ensemble = EnsembleVotingClassifier()
        level = ensemble._determine_threat_level(3, 3)
        assert level == ThreatLevel.CRITICAL


class TestFactoryFunction:
    """Test factory function."""
    
    def test_create_ensemble_classifier(self):
        """Test factory function."""
        ensemble = create_ensemble_classifier(contamination=0.15, random_state=123)
        
        assert ensemble is not None
        assert ensemble.contamination == 0.15
        assert ensemble.random_state == 123
    
    def test_create_ensemble_default_params(self):
        """Test factory dengan parameter default."""
        ensemble = create_ensemble_classifier()
        
        assert ensemble.contamination == 0.1
        assert ensemble.random_state == 42


class TestEdgeCases:
    """Test edge cases dan error handling."""
    
    @pytest.fixture
    def trained_ensemble(self):
        np.random.seed(42)
        X_train = np.random.randn(50, 6)  # Reduced for speed
        ensemble = EnsembleVotingClassifier(contamination=0.1)
        ensemble.fit(X_train)
        return ensemble
    
    def test_1d_input_conversion(self, trained_ensemble):
        """Test bahwa 1D input dikonversi ke 2D."""
        X_1d = np.array([5.0, 5.0, 5.0, 5.0, 5.0, 5.0])
        
        # Seharusnya tidak error
        result = trained_ensemble.predict(X_1d)
        
        assert result is not None
    
    def test_extreme_values(self, trained_ensemble):
        """Test dengan nilai ekstrem."""
        X_extreme = np.array([[1e10, -1e10, 1e10, -1e10, 1e10, -1e10]])
        
        result = trained_ensemble.predict(X_extreme)
        
        # Harus tetap memberikan hasil valid
        assert result.threat_level in ThreatLevel
        assert 0 <= result.consensus_score <= 1
    
    def test_zero_values(self, trained_ensemble):
        """Test dengan semua nilai nol."""
        X_zeros = np.array([[0, 0, 0, 0, 0, 0]])
        
        result = trained_ensemble.predict(X_zeros)
        
        assert result is not None


if __name__ == '__main__':
    pytest.main([__file__, '-v'])
