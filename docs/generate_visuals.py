"""
========================================
LOG SENTINEL - Visualization Generator
Kode Python untuk Generate Aset Visual Presentasi
========================================

INSTRUKSI:
1. Pastikan dependencies terinstall: pip install matplotlib seaborn numpy matplotlib-venn
2. Run script ini: python generate_visuals.py
3. File PNG akan tersimpan di folder yang sama

OUTPUT FILES:
- slide3_venn_diagram.png
- slide4_pie_chart.png
- slide5_feature_importance.png
- slide6_isolation_forest_scatter.png
- slide9_confusion_matrix.png
- slide10_attack_comparison.png

Tim Pengembang:
- JEREMY CHRISTO EMMANUELLE PANJAITAN (237006516084)
- MUHAMMAD AKBAR HADI PRATAMA (237006516058)
- FARREL ALFARIDZI (237006516028)
- CHOSMAS LAURENS RUMNGEWUR (217006516074)
"""

import matplotlib.pyplot as plt
import matplotlib.patches as mpatches
from matplotlib.patches import Circle, FancyBboxPatch
import seaborn as sns
import numpy as np
from matplotlib_venn import venn2, venn2_circles

# ========================================
# GLOBAL STYLE SETTINGS - CYBER THEME
# ========================================

# Dark cyber color palette
DARK_BG = '#0a0a0a'
DARK_CARD = '#1a1a2e'
NEON_GREEN = '#00ff88'
NEON_RED = '#ff4757'
NEON_BLUE = '#3498db'
NEON_ORANGE = '#f39c12'
NEON_PURPLE = '#9b59b6'
TEXT_WHITE = '#ffffff'
TEXT_GRAY = '#aaaaaa'

# Set global style
plt.style.use('dark_background')
plt.rcParams['font.family'] = 'sans-serif'
plt.rcParams['font.sans-serif'] = ['Segoe UI', 'Arial', 'DejaVu Sans']
plt.rcParams['font.size'] = 12
plt.rcParams['axes.facecolor'] = DARK_BG
plt.rcParams['figure.facecolor'] = DARK_BG
plt.rcParams['savefig.facecolor'] = DARK_BG
plt.rcParams['text.color'] = TEXT_WHITE
plt.rcParams['axes.labelcolor'] = TEXT_WHITE
plt.rcParams['xtick.color'] = TEXT_WHITE
plt.rcParams['ytick.color'] = TEXT_WHITE


# ========================================
# SLIDE 3: VENN DIAGRAM
# Machine Learning + Cybersecurity = Log Sentinel
# ========================================

def generate_slide3_venn():
    """
    Diagram Venn: Irisan Machine Learning dan Cybersecurity
    """
    print("[SLIDE 3] Generating Venn Diagram...")
    
    fig, ax = plt.subplots(figsize=(12, 8))
    fig.patch.set_facecolor(DARK_BG)
    ax.set_facecolor(DARK_BG)
    
    # Create Venn diagram
    v = venn2(
        subsets=(40, 40, 20),  # (ML only, Cyber only, Intersection)
        set_labels=('', ''),  # We'll add custom labels
        set_colors=(NEON_BLUE, NEON_RED),
        alpha=0.7,
        ax=ax
    )
    
    # Style the circles
    c = venn2_circles(subsets=(40, 40, 20), linestyle='solid', linewidth=3, color=TEXT_WHITE, ax=ax)
    
    # Customize labels
    # Left circle - Machine Learning
    if v.get_label_by_id('10'):
        v.get_label_by_id('10').set_text('Supervised\nUnsupervised\nFeature Eng.\nPCA/Clustering')
        v.get_label_by_id('10').set_fontsize(11)
        v.get_label_by_id('10').set_color(TEXT_WHITE)
        v.get_label_by_id('10').set_fontweight('normal')
    
    # Right circle - Cybersecurity
    if v.get_label_by_id('01'):
        v.get_label_by_id('01').set_text('Firewall\nIDS/IPS\nSOC\nIncident Response')
        v.get_label_by_id('01').set_fontsize(11)
        v.get_label_by_id('01').set_color(TEXT_WHITE)
        v.get_label_by_id('01').set_fontweight('normal')
    
    # Intersection - Log Sentinel
    if v.get_label_by_id('11'):
        v.get_label_by_id('11').set_text('LOG\nSENTINEL')
        v.get_label_by_id('11').set_fontsize(16)
        v.get_label_by_id('11').set_color(NEON_GREEN)
        v.get_label_by_id('11').set_fontweight('bold')
    
    # Add set titles
    ax.text(-0.55, 0.35, 'MACHINE\nLEARNING', fontsize=18, fontweight='bold', 
            ha='center', va='center', color=NEON_BLUE)
    ax.text(0.55, 0.35, 'CYBER\nSECURITY', fontsize=18, fontweight='bold', 
            ha='center', va='center', color=NEON_RED)
    
    # Title
    ax.set_title('Integrasi AI + Cybersecurity', fontsize=22, fontweight='bold', 
                 color=TEXT_WHITE, pad=20)
    
    # Subtitle
    ax.text(0, -0.55, 'Anomaly Detection System menggunakan Isolation Forest', 
            fontsize=12, ha='center', va='center', color=TEXT_GRAY, style='italic')
    
    ax.axis('off')
    plt.tight_layout()
    plt.savefig('slide3_venn_diagram.png', dpi=300, bbox_inches='tight', 
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 3] ✓ Saved: slide3_venn_diagram.png")


# ========================================
# SLIDE 4: PIE CHART
# Traffic Distribution: 90% Normal, 10% Anomaly
# ========================================

def generate_slide4_pie():
    """
    Pie Chart: Distribusi Traffic Normal vs Anomali
    Based on contamination=0.1 (10% anomaly expected)
    """
    print("[SLIDE 4] Generating Pie Chart...")
    
    fig, ax = plt.subplots(figsize=(10, 8))
    fig.patch.set_facecolor(DARK_BG)
    ax.set_facecolor(DARK_BG)
    
    # Data based on contamination=0.1
    sizes = [90, 10]
    labels = ['Normal Traffic\n(90%)', 'Anomaly Traffic\n(10%)']
    colors = [NEON_BLUE, NEON_RED]
    explode = (0, 0.1)  # Explode the anomaly slice
    
    # Create pie chart
    wedges, texts, autotexts = ax.pie(
        sizes, 
        explode=explode,
        labels=labels,
        colors=colors,
        autopct='%1.0f%%',
        shadow=True,
        startangle=90,
        textprops={'fontsize': 14, 'color': TEXT_WHITE},
        wedgeprops={'edgecolor': TEXT_WHITE, 'linewidth': 2}
    )
    
    # Style autopct
    for autotext in autotexts:
        autotext.set_color(TEXT_WHITE)
        autotext.set_fontweight('bold')
        autotext.set_fontsize(16)
    
    # Title
    ax.set_title('Distribusi Dataset Log Sentinel\n(1000 Records)', 
                 fontsize=20, fontweight='bold', color=TEXT_WHITE, pad=20)
    
    # Legend
    legend_elements = [
        mpatches.Patch(facecolor=NEON_BLUE, edgecolor=TEXT_WHITE, label='Normal: 900 records'),
        mpatches.Patch(facecolor=NEON_RED, edgecolor=TEXT_WHITE, label='Anomaly: 100 records')
    ]
    ax.legend(handles=legend_elements, loc='lower center', fontsize=11, 
              facecolor=DARK_CARD, edgecolor=TEXT_WHITE, labelcolor=TEXT_WHITE,
              bbox_to_anchor=(0.5, -0.1))
    
    # Subtitle
    fig.text(0.5, 0.02, 'Parameter: contamination=0.1 | n_estimators=100 | random_state=42',
             ha='center', fontsize=10, color=TEXT_GRAY, style='italic')
    
    ax.axis('equal')
    plt.tight_layout()
    plt.savefig('slide4_pie_chart.png', dpi=300, bbox_inches='tight',
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 4] ✓ Saved: slide4_pie_chart.png")


# ========================================
# SLIDE 5: FEATURE IMPORTANCE BAR CHART
# Horizontal bar chart with cyber theme
# ========================================

def generate_slide5_feature_importance():
    """
    Horizontal Bar Chart: Feature Importance untuk Threat Detection
    Order: Status Code (highest) -> HTTP Method (lowest)
    """
    print("[SLIDE 5] Generating Feature Importance Chart...")
    
    fig, ax = plt.subplots(figsize=(12, 7))
    fig.patch.set_facecolor(DARK_BG)
    ax.set_facecolor(DARK_CARD)
    
    # Feature importance data (hardcoded as specified)
    features = ['HTTP Method', 'IP Frequency', 'URL Length', 'Response Time', 'Status Code']
    importance = [0.12, 0.18, 0.22, 0.25, 0.32]
    
    # Color gradient from low to high importance
    colors = [NEON_PURPLE, NEON_BLUE, NEON_ORANGE, NEON_RED, NEON_GREEN]
    
    # Create horizontal bar chart
    bars = ax.barh(features, importance, color=colors, edgecolor=TEXT_WHITE, linewidth=1.5, height=0.6)
    
    # Add value labels
    for bar, imp in zip(bars, importance):
        width = bar.get_width()
        ax.annotate(f'{imp:.0%}',
                    xy=(width + 0.01, bar.get_y() + bar.get_height() / 2),
                    ha='left', va='center',
                    fontsize=14, fontweight='bold', color=TEXT_WHITE)
    
    # Add rank labels
    for i, (bar, feature) in enumerate(zip(bars, features)):
        rank = len(features) - i
        ax.annotate(f'#{rank}',
                    xy=(0.01, bar.get_y() + bar.get_height() / 2),
                    ha='left', va='center',
                    fontsize=12, fontweight='bold', color=DARK_BG)
    
    # Styling
    ax.set_xlabel('Importance Score', fontsize=14, color=TEXT_WHITE, labelpad=10)
    ax.set_ylabel('Features', fontsize=14, color=TEXT_WHITE, labelpad=10)
    ax.set_title('Feature Importance untuk Deteksi Anomali', 
                 fontsize=18, fontweight='bold', color=TEXT_WHITE, pad=15)
    
    # Grid
    ax.xaxis.grid(True, linestyle='--', alpha=0.3, color=TEXT_GRAY)
    ax.set_axisbelow(True)
    
    # Set x limit
    ax.set_xlim(0, 0.42)
    
    # Spine styling
    for spine in ax.spines.values():
        spine.set_color(TEXT_GRAY)
        spine.set_linewidth(1)
    
    # Subtitle
    fig.text(0.5, 0.02, 'Status Code adalah indikator paling kuat — DDoS triggers 503, BruteForce triggers 401',
             ha='center', fontsize=10, color=TEXT_GRAY, style='italic')
    
    plt.tight_layout()
    plt.savefig('slide5_feature_importance.png', dpi=300, bbox_inches='tight',
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 5] ✓ Saved: slide5_feature_importance.png")


# ========================================
# SLIDE 6: ISOLATION FOREST SCATTER PLOT
# 200 normal points clustered + 20 anomaly points scattered
# ========================================

def generate_slide6_isolation_scatter():
    """
    Scatter Plot 2D: Ilustrasi prinsip kerja Isolation Forest
    - 200 titik biru (normal) menggerombol di tengah
    - 20 titik merah (anomali) tersebar jauh/terisolasi
    """
    print("[SLIDE 6] Generating Isolation Forest Scatter Plot...")
    
    np.random.seed(42)  # Reproducibility matching model
    
    fig, ax = plt.subplots(figsize=(12, 8))
    fig.patch.set_facecolor(DARK_BG)
    ax.set_facecolor(DARK_CARD)
    
    # Generate normal points - clustered in center
    n_normal = 200
    normal_x = np.random.normal(0, 1, n_normal)
    normal_y = np.random.normal(0, 1, n_normal)
    
    # Generate anomaly points - scattered far from center
    n_anomaly = 20
    # Create anomalies in different distant regions
    anomaly_angles = np.random.uniform(0, 2 * np.pi, n_anomaly)
    anomaly_distances = np.random.uniform(3.5, 5.5, n_anomaly)
    anomaly_x = anomaly_distances * np.cos(anomaly_angles)
    anomaly_y = anomaly_distances * np.sin(anomaly_angles)
    
    # Plot normal points
    ax.scatter(normal_x, normal_y, c=NEON_BLUE, s=60, alpha=0.7, 
               label='Normal Traffic (200)', edgecolors=TEXT_WHITE, linewidth=0.5)
    
    # Plot anomaly points with glow effect
    ax.scatter(anomaly_x, anomaly_y, c=NEON_RED, s=120, alpha=0.9,
               label='Anomaly Traffic (20)', edgecolors=TEXT_WHITE, linewidth=1.5,
               marker='D')  # Diamond marker for anomalies
    
    # Add annotation arrows for some anomalies
    for i in range(0, n_anomaly, 5):
        ax.annotate('Isolated!', xy=(anomaly_x[i], anomaly_y[i]),
                    xytext=(anomaly_x[i] + 0.8, anomaly_y[i] + 0.8),
                    fontsize=9, color=NEON_RED,
                    arrowprops=dict(arrowstyle='->', color=NEON_RED, lw=1.5))
    
    # Add center cluster annotation
    ax.annotate('Normal Cluster\n(Dense Region)', xy=(0, 0),
                xytext=(-3, -4), fontsize=11, color=NEON_BLUE,
                ha='center',
                arrowprops=dict(arrowstyle='->', color=NEON_BLUE, lw=2))
    
    # Draw isolation boundary (conceptual)
    circle = plt.Circle((0, 0), 2.5, fill=False, color=NEON_GREEN, 
                         linestyle='--', linewidth=2, alpha=0.6)
    ax.add_patch(circle)
    ax.text(2.7, 0, 'Isolation\nBoundary', fontsize=10, color=NEON_GREEN, 
            va='center', style='italic')
    
    # Styling
    ax.set_xlabel('Principal Component 1 (PC1)', fontsize=12, color=TEXT_WHITE)
    ax.set_ylabel('Principal Component 2 (PC2)', fontsize=12, color=TEXT_WHITE)
    ax.set_title('Prinsip Kerja Isolation Forest\nAnomali Lebih Mudah Diisolasi', 
                 fontsize=16, fontweight='bold', color=TEXT_WHITE, pad=15)
    
    # Legend
    ax.legend(loc='upper right', fontsize=11, facecolor=DARK_CARD, 
              edgecolor=TEXT_WHITE, labelcolor=TEXT_WHITE)
    
    # Grid
    ax.grid(True, linestyle='--', alpha=0.2, color=TEXT_GRAY)
    
    # Equal aspect
    ax.set_aspect('equal')
    ax.set_xlim(-7, 7)
    ax.set_ylim(-7, 7)
    
    # Spine styling
    for spine in ax.spines.values():
        spine.set_color(TEXT_GRAY)
    
    # Subtitle
    fig.text(0.5, 0.02, 'Titik di luar boundary = membutuhkan sedikit split untuk diisolasi = ANOMALI',
             ha='center', fontsize=10, color=TEXT_GRAY, style='italic')
    
    plt.tight_layout()
    plt.savefig('slide6_isolation_forest_scatter.png', dpi=300, bbox_inches='tight',
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 6] ✓ Saved: slide6_isolation_forest_scatter.png")


# ========================================
# SLIDE 8: DASHBOARD CONCEPT
# Image Generation Prompt (for Midjourney/DALL-E)
# ========================================

def generate_slide8_prompt():
    """
    Generate detailed image prompt for AI image generation tools
    """
    print("[SLIDE 8] Generating Image Prompt...")
    
    prompt = """
========================================
🎨 PROMPT UNTUK MIDJOURNEY / DALL-E
Dashboard Concept - Log Sentinel
========================================

--- ENGLISH PROMPT (Recommended) ---

"Futuristic cybersecurity command center dashboard, dark mode UI, black background (#0a0a0a). 

Main feature: Interactive world map in center with dark continent outlines. Multiple curved NEON GREEN (#00ff88) attack lines originating from various countries (Russia, China, USA, Brazil) all converging to a single glowing target point in INDONESIA (Jakarta). 

Left panel: Real-time statistics cards showing "Total Requests: 15,234", "Anomalies: 1,523", "Threat Level: 10%", with green/red indicators. 

Right panel: Recent activity log table with color-coded rows - green for normal, red for anomaly. 

Top bar: "LOG SENTINEL" logo with shield icon, system status "ACTIVE" with pulsing green dot. 

Style: Clean UI, glassmorphism effects, subtle grid overlay, professional enterprise security software aesthetic. 

Technical elements: IP addresses visible in popups, severity badges (Critical/High/Medium), timestamps.

Mood: Vigilant, high-tech, mission control atmosphere.

--ar 16:9 --v 6"


--- PROMPT INDONESIA (Alternative) ---

"Dashboard pusat komando keamanan siber futuristik, UI mode gelap, latar belakang hitam. 

Fitur utama: Peta dunia interaktif di tengah dengan outline benua gelap. Beberapa garis serangan HIJAU NEON melengkung berasal dari berbagai negara (Rusia, China, USA, Brasil) semuanya menuju satu titik target yang menyala di INDONESIA (Jakarta).

Panel kiri: Kartu statistik real-time. Panel kanan: Tabel log aktivitas terkini.

Gaya: UI bersih, efek glassmorphism, estetika software keamanan enterprise profesional.

--ar 16:9 --v 6"

========================================
    """
    
    # Save prompt to file
    with open('slide8_dashboard_prompt.txt', 'w', encoding='utf-8') as f:
        f.write(prompt)
    
    print("[SLIDE 8] ✓ Saved: slide8_dashboard_prompt.txt")
    print(prompt)
    return prompt


# ========================================
# SLIDE 9: CONFUSION MATRIX HEATMAP
# EXACT DATA: TN=850, FP=50, FN=12, TP=88
# ========================================

def generate_slide9_confusion_matrix():
    """
    Confusion Matrix Heatmap dengan data EXACT:
    TN=850, FP=50, FN=12, TP=88
    """
    print("[SLIDE 9] Generating Confusion Matrix Heatmap...")
    
    fig, ax = plt.subplots(figsize=(10, 8))
    fig.patch.set_facecolor(DARK_BG)
    
    # EXACT DATA as specified - DO NOT CHANGE
    confusion_matrix = np.array([
        [850, 50],   # Actual Normal: TN=850, FP=50
        [12, 88]     # Actual Anomaly: FN=12, TP=88
    ])
    
    # Custom colormap - dark theme
    cmap = sns.color_palette("RdYlGn_r", as_cmap=True)
    
    # Create heatmap
    sns.heatmap(
        confusion_matrix,
        annot=True,
        fmt='d',
        cmap='RdYlBu_r',
        linewidths=3,
        linecolor=TEXT_WHITE,
        square=True,
        cbar_kws={'label': 'Count', 'shrink': 0.8},
        annot_kws={'size': 28, 'weight': 'bold', 'color': TEXT_WHITE},
        ax=ax
    )
    
    # Labels
    ax.set_xticklabels(['Predicted\nNORMAL', 'Predicted\nANOMALY'], fontsize=14, color=TEXT_WHITE)
    ax.set_yticklabels(['Actual\nNORMAL', 'Actual\nANOMALY'], fontsize=14, color=TEXT_WHITE, rotation=0, va='center')
    
    ax.set_xlabel('Predicted Label', fontsize=14, color=TEXT_WHITE, labelpad=15)
    ax.set_ylabel('Actual Label', fontsize=14, color=TEXT_WHITE, labelpad=15)
    
    # Title
    ax.set_title('Confusion Matrix - Model Evaluation\n(Total: 1000 samples)', 
                 fontsize=18, fontweight='bold', color=TEXT_WHITE, pad=20)
    
    # Add cell labels
    ax.text(0.5, -0.15, 'TN = 850', transform=ax.transAxes, ha='center', fontsize=11, color=NEON_GREEN)
    ax.text(0.5, -0.22, 'True Negatives: Normal diprediksi Normal ✓', transform=ax.transAxes, ha='center', fontsize=9, color=TEXT_GRAY)
    
    # Metrics box
    metrics_text = """
    ╔════════════════════════════════╗
    ║  METRICS SUMMARY               ║
    ╠════════════════════════════════╣
    ║  Accuracy  = 938/1000 = 93.8%  ║
    ║  Precision = 88/138   = 63.8%  ║
    ║  Recall    = 88/100   = 88.0%  ║
    ║  F1-Score  = 2×P×R/(P+R)= 74%  ║
    ╚════════════════════════════════╝
    """
    
    # Add metrics annotation - calculate correct values
    accuracy = (850 + 88) / 1000
    precision = 88 / (88 + 50)
    recall = 88 / (88 + 12)
    f1 = 2 * precision * recall / (precision + recall)
    
    metrics_str = f'Accuracy: {accuracy:.1%} | Precision: {precision:.1%} | Recall: {recall:.1%} | F1: {f1:.1%}'
    fig.text(0.5, 0.02, metrics_str, ha='center', fontsize=12, 
             color=NEON_GREEN, fontweight='bold', 
             bbox=dict(boxstyle='round', facecolor=DARK_CARD, edgecolor=NEON_GREEN))
    
    # Colorbar styling
    cbar = ax.collections[0].colorbar
    cbar.ax.yaxis.set_tick_params(color=TEXT_WHITE)
    cbar.ax.yaxis.label.set_color(TEXT_WHITE)
    plt.setp(plt.getp(cbar.ax.axes, 'yticklabels'), color=TEXT_WHITE)
    
    plt.tight_layout()
    plt.savefig('slide9_confusion_matrix.png', dpi=300, bbox_inches='tight',
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 9] ✓ Saved: slide9_confusion_matrix.png")
    print(f"[SLIDE 9] Metrics: Acc={accuracy:.1%}, Prec={precision:.1%}, Rec={recall:.1%}, F1={f1:.1%}")


# ========================================
# SLIDE 10: ATTACK SUCCESS RATE COMPARISON
# Without vs With Log Sentinel
# ========================================

def generate_slide10_comparison():
    """
    Grouped Bar Chart: Attack Success Rate
    Without Log Sentinel (80%) vs With Log Sentinel (5%)
    """
    print("[SLIDE 10] Generating Attack Comparison Chart...")
    
    fig, ax = plt.subplots(figsize=(12, 7))
    fig.patch.set_facecolor(DARK_BG)
    ax.set_facecolor(DARK_CARD)
    
    # Data
    categories = ['Attack Success Rate', 'System Vulnerability', 'Response Time']
    without_sentinel = [80, 75, 70]  # Percentages
    with_sentinel = [5, 10, 15]      # Percentages
    
    x = np.arange(len(categories))
    width = 0.35
    
    # Create bars
    bars1 = ax.bar(x - width/2, without_sentinel, width, label='Without Log Sentinel',
                   color=NEON_RED, edgecolor=TEXT_WHITE, linewidth=1.5)
    bars2 = ax.bar(x + width/2, with_sentinel, width, label='With Log Sentinel',
                   color=NEON_GREEN, edgecolor=TEXT_WHITE, linewidth=1.5)
    
    # Add value labels on bars
    for bar in bars1:
        height = bar.get_height()
        ax.annotate(f'{height}%',
                    xy=(bar.get_x() + bar.get_width() / 2, height),
                    xytext=(0, 5), textcoords="offset points",
                    ha='center', va='bottom', fontsize=14, fontweight='bold', color=NEON_RED)
    
    for bar in bars2:
        height = bar.get_height()
        ax.annotate(f'{height}%',
                    xy=(bar.get_x() + bar.get_width() / 2, height),
                    xytext=(0, 5), textcoords="offset points",
                    ha='center', va='bottom', fontsize=14, fontweight='bold', color=NEON_GREEN)
    
    # Add reduction arrows
    for i, (w, wo) in enumerate(zip(with_sentinel, without_sentinel)):
        reduction = wo - w
        ax.annotate('', xy=(i, w + 3), xytext=(i, wo - 3),
                    arrowprops=dict(arrowstyle='->', color=TEXT_WHITE, lw=2))
        ax.text(i, (w + wo) / 2, f'↓{reduction}%', ha='center', va='center',
                fontsize=12, fontweight='bold', color=TEXT_WHITE,
                bbox=dict(boxstyle='round', facecolor=DARK_BG, edgecolor=NEON_GREEN))
    
    # Styling
    ax.set_ylabel('Percentage (%)', fontsize=14, color=TEXT_WHITE)
    ax.set_title('Efektivitas Log Sentinel dalam Mitigasi Ancaman', 
                 fontsize=18, fontweight='bold', color=TEXT_WHITE, pad=15)
    ax.set_xticks(x)
    ax.set_xticklabels(categories, fontsize=12, color=TEXT_WHITE)
    ax.legend(loc='upper right', fontsize=12, facecolor=DARK_CARD, 
              edgecolor=TEXT_WHITE, labelcolor=TEXT_WHITE)
    
    # Set y limit
    ax.set_ylim(0, 100)
    
    # Grid
    ax.yaxis.grid(True, linestyle='--', alpha=0.3, color=TEXT_GRAY)
    ax.set_axisbelow(True)
    
    # Spine styling
    for spine in ax.spines.values():
        spine.set_color(TEXT_GRAY)
    
    # Summary box
    summary_text = 'HASIL: Penurunan Attack Success Rate dari 80% → 5% (Reduksi 75 percentage points)'
    fig.text(0.5, 0.02, summary_text, ha='center', fontsize=11, 
             color=NEON_GREEN, fontweight='bold',
             bbox=dict(boxstyle='round', facecolor=DARK_CARD, edgecolor=NEON_GREEN))
    
    plt.tight_layout()
    plt.savefig('slide10_attack_comparison.png', dpi=300, bbox_inches='tight',
                facecolor=DARK_BG, edgecolor='none')
    plt.close()
    print("[SLIDE 10] ✓ Saved: slide10_attack_comparison.png")


# ========================================
# MAIN EXECUTION
# ========================================

def main():
    print("=" * 60)
    print("  LOG SENTINEL - Visualization Generator")
    print("  Generating High-Resolution Assets for Presentation")
    print("=" * 60)
    print()
    
    # Generate all visualizations
    generate_slide3_venn()
    generate_slide4_pie()
    generate_slide5_feature_importance()
    generate_slide6_isolation_scatter()
    generate_slide8_prompt()
    generate_slide9_confusion_matrix()
    generate_slide10_comparison()
    
    print()
    print("=" * 60)
    print("  ✅ ALL VISUALIZATIONS GENERATED SUCCESSFULLY!")
    print("=" * 60)
    print()
    print("Output files:")
    print("  📊 slide3_venn_diagram.png")
    print("  📊 slide4_pie_chart.png")
    print("  📊 slide5_feature_importance.png")
    print("  📊 slide6_isolation_forest_scatter.png")
    print("  📝 slide8_dashboard_prompt.txt")
    print("  📊 slide9_confusion_matrix.png")
    print("  📊 slide10_attack_comparison.png")
    print()
    print("Resolution: 300 DPI (High-res for presentation)")
    print("Theme: Cyber Dark Mode")


if __name__ == '__main__':
    main()
