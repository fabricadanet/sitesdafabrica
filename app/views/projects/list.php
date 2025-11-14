<?php
// app/views/projects/list.php
// Dashboard do usuário com cards informativos e gerenciamento de plano
// MELHORADO: Adicionados botões de logout e painel admin
require_once __DIR__ . '/../../helpers/subscription.php';

?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Projetos — Sites da Fábrica</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-main {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ===== HEADER ===== */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            color: white;
            flex-wrap: wrap;
            gap: 15px;
        }

        .header-title h1 {
            font-size: 32px;
            font-weight: 700;
            margin: 0;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .user-info {
            color: white;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.1);
            padding: 8px 12px;
            border-radius: 6px;
            backdrop-filter: blur(10px);
        }

        .user-badge {
            background: rgba(255, 255, 255, 0.3);
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .user-badge.admin {
            background: #ff9800;
            color: white;
        }

        .btn-new-project {
            background: white;
            color: #667eea;
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-new-project:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            background: #f0f0f0;
        }

        .btn-admin {
            background: #ff9800;
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-admin:hover {
            background: #f57c00;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .btn-logout {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            padding: 10px 16px;
            border-radius: 8px;
            border: 1px solid rgba(255, 255, 255, 0.5);
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-logout:hover {
            background: rgba(255, 0, 0, 0.3);
            border-color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            color: white;
            text-decoration: none;
        }

        .btn-logout:active {
            transform: translateY(0);
        }

        /* ===== CARDS INFORMATIVOS ===== */
        .cards-section {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .info-card {
            background: white;
            border-radius: 12px;
            padding: 24px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            font-size: 14px;
            color: #999;
            margin-bottom: 10px;
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: 32px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }

        .card-description {
            font-size: 13px;
            color: #999;
            line-height: 1.5;
        }

        .card-badge {
            display: inline-block;
            background: #e8eaf6;
            color: #667eea;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            margin-top: 10px;
        }

        /* ===== SEÇÃO DE PROJETOS ===== */
        .projects-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-bottom: 40px;
        }

        .section-title {
            font-size: 20px;
            font-weight: 700;
            color: #333;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #667eea;
        }

        .projects-table {
            width: 100%;
            border-collapse: collapse;
        }

        .projects-table thead tr {
            border-bottom: 2px solid #eee;
            background: #f9fafb;
        }

        .projects-table th {
            padding: 16px 12px;
            text-align: left;
            font-weight: 600;
            color: #666;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .projects-table td {
            padding: 16px 12px;
            border-bottom: 1px solid #eee;
            color: #333;
        }

        .projects-table tbody tr:hover {
            background: #f9fafb;
        }

        .project-name {
            font-weight: 600;
            color: #667eea;
            cursor: pointer;
            transition: color 0.3s;
        }

        .project-name:hover {
            color: #5568d3;
            text-decoration: underline;
        }

        .project-date {
            color: #999;
            font-size: 13px;
        }

        .project-actions {
            display: flex;
            gap: 8px;
        }

        .btn-action {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-edit {
            background: #e3f2fd;
            color: #1976d2;
            text-decoration: none;
        }

        .btn-edit:hover {
            background: #bbdefb;
            color: #1565c0;
            text-decoration: none;
        }

        .btn-delete {
            background: #ffebee;
            color: #c62828;
        }

        .btn-delete:hover {
            background: #ffcdd2;
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        .empty-state-text {
            font-size: 18px;
            margin-bottom: 20px;
        }

        .btn-create-first {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-create-first:hover {
            background: #5568d3;
        }

        /* ===== MODALS ===== */
        .modal-content {
            border: none;
            border-radius: 12px;
        }

        .modal-header {
            background: #f9fafb;
            border-bottom: 1px solid #eee;
        }

        .modal-header .modal-title {
            font-weight: 700;
            color: #333;
        }

        .modal-body {
            padding: 25px 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-help {
            color: #999;
            font-size: 12px;
            margin-top: 4px;
        }

        .modal-footer {
            background: #f9fafb;
            border-top: 1px solid #eee;
            padding: 15px 20px;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-modal {
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            font-size: 14px;
        }

        .btn-primary-modal {
            background: #667eea;
            color: white;
        }

        .btn-primary-modal:hover {
            background: #5568d3;
        }

        .btn-secondary-modal {
            background: #eee;
            color: #333;
        }

        .btn-secondary-modal:hover {
            background: #ddd;
        }

        /* ===== UPGRADE CARDS ===== */
        .upgrade-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .plan-card {
            background: white;
            border: 2px solid #eee;
            border-radius: 12px;
            padding: 20px;
            transition: all 0.3s;
            cursor: pointer;
        }

        .plan-card:hover {
            border-color: #667eea;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.15);
        }

        .plan-card.current {
            border-color: #667eea;
            background: #f9fafb;
        }

        .plan-name-header {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .plan-price {
            font-size: 24px;
            font-weight: 700;
            color: #667eea;
            margin-bottom: 15px;
        }

        .plan-price-period {
            color: #999;
            font-size: 14px;
        }

        .plan-features {
            list-style: none;
            margin: 15px 0;
        }

        .plan-features li {
            padding: 8px 0;
            color: #666;
            font-size: 13px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .plan-features i {
            color: #4caf50;
            font-size: 12px;
        }

        .upgrade-button {
            width: 100%;
            background: #667eea;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .upgrade-button:hover {
            background: #5568d3;
        }

        .upgrade-button.current {
            background: #ddd;
            color: #666;
            cursor: default;
        }

        .upgrade-button.current:hover {
            background: #ddd;
        }

        .current-badge {
            display: inline-block;
            background: #4caf50;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        /* ===== PROFILE SECTION ===== */
        .profile-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .btn-update-profile {
            background: #667eea;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
        }

        .btn-update-profile:hover {
            background: #5568d3;
        }

        @media (max-width: 768px) {
            .header-section {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-left {
                width: 100%;
                flex-direction: column;
            }

            .header-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .projects-table {
                font-size: 13px;
            }

            .projects-table th,
            .projects-table td {
                padding: 12px 8px;
            }
        }
    </style>
</head>

<body>

<div class="container-main">

    <!-- ===== HEADER ===== -->
    <div class="header-section">
        <div class="header-left">
            <div class="header-title">
                <h1>⚡ Meus Projetos</h1>
            </div>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?= htmlspecialchars($userData['name'] ?? 'Usuário') ?></span>
                <?php if (isset($userData['role']) && $userData['role'] === 'admin'): ?>
                    <span class="user-badge admin">Admin</span>
                <?php endif; ?>
            </div>
        </div>

        <div class="header-actions">
            <button class="btn-new-project" onclick="showNewProjectModal()">
                <i class="fas fa-plus"></i> Novo Projeto
            </button>

            <?php if (isset($userData['role']) && $userData['role'] === 'admin'): ?>
                <a href="/admin" class="btn-admin">
                    <i class="fas fa-cog"></i> Painel Admin
                </a>
            <?php endif; ?>

            <a href="/logout" class="btn-logout" onclick="return confirm('Tem certeza que deseja fazer logout?')">
                <i class="fas fa-sign-out-alt"></i> Sair
            </a>
        </div>
    </div>

    <!-- ===== CARDS INFORMATIVOS ===== -->
    <div class="cards-section">
        <!-- Plano Atual -->
        <div class="info-card">
            <div class="card-title">💳 Plano Atual</div>
            <div class="card-value"><?= htmlspecialchars($planData['name'] ?? 'Gratuito') ?></div>
            <div class="card-description">
                <?= htmlspecialchars($planData['description'] ?? 'Plano padrão') ?>
            </div>
            <div class="card-badge">
                Renova em <?= $subscriptionData['renews_at'] ?? date('d/m/Y', strtotime('+30 days')) ?>
            </div>
        </div>

        <!-- Projetos Utilizados -->
        <div class="info-card">
            <div class="card-title">📊 Projetos</div>
            <div class="card-value"><?= $totalProjects ?>/<?= $planData['max_projects'] ?? 3 ?></div>
            <div class="card-description">
                Você criou <strong><?= $totalProjects ?></strong> projeto(s) do limite de <strong><?= $planData['max_projects'] ?? 3 ?></strong>
            </div>
            <div class="card-badge">
                <?= ($totalProjects < ($planData['max_projects'] ?? 3)) ? 'Espaço disponível' : 'Limite atingido' ?>
            </div>
        </div>

        <!-- Templates Preminum-->
        <div class="info-card">
            <div class="card-title">Templates Premium </div>
            <div class="card-value">
                <?php if ($planData['can_access_premium'] === 1): ?>
                    Todos Acessíveis
                <?php else: ?>
                    Limitado
                <?php endif; ?>
            </div>
            <?php if ($planData['can_access_premium'] === 1): ?>
                <div class="card-description">
                    Acesso a todos os templates premium
                </div>
            <?php else: ?>
            <div class="card-description">
                Faça um upgrade para acessar templates exclusivos
            </div>
            <?php endif; ?>
        </div>

        <!-- quantidade de dominoios e subdominios-->
        


    </div>

    <!-- ===== SEÇÃO DE PROJETOS ===== -->
    <div class="projects-section">
        <div class="section-title">
            <i class="fas fa-folder"></i> Seus Projetos
        </div>

        <?php if (!empty($projects)): ?>
            <table class="projects-table">
                <thead>
                <tr>
                    <th>Nome</th>
                    <th>Template</th>
                    <th>Atualizado em</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($projects as $project): ?>
                    <tr>
                        <td>
                                    <span class="project-name" onclick="editProject(<?= $project['id'] ?>, <?= $project['template_id'] ?? 'null' ?>)">

                                        <?= htmlspecialchars($project['name']) ?>
                                    </span>
                        </td>
                        <td>
                            <?= htmlspecialchars($project['template_name'] ?? 'Padrão') ?>
                        </td>
                        <td>
                                    <span class="project-date">
                                        <?= date('d/m/Y H:i', strtotime($project['updated_at'])) ?>
                                    </span>
                        </td>
                        <td>
                            <div class="project-actions">
                                <a href="/editor?id=<?= $project['id'] ?>&template=<?= $project['template_id'] ?>"
                                   class="btn-action btn-edit">

                                <i class="fas fa-edit"></i> Editar
                                </a>
                                <button class="btn-action btn-delete" onclick="deleteProject(<?= $project['id'] ?>)">
                                    <i class="fas fa-trash"></i> Deletar
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <div class="empty-state-icon">📂</div>
                <div class="empty-state-text">Nenhum projeto criado ainda</div>
                <button class="btn-create-first" onclick="showNewProjectModal()">
                    Criar Primeiro Projeto
                </button>
            </div>
        <?php endif; ?>
    </div>

    <!-- ===== SEÇÃO DE UPGRADE ===== -->
    <?php if ($planData['plan_id'] !== 'Premium'): ?>
               <div class="projects-section">
            <div class="section-title">
                <i class="fas fa-rocket"></i> Faça um Upgrade
            </div>
            <div class="upgrade-cards" id="plansContainer">
                <!-- Planos serão carregados via JavaScript -->
            </div>
        </div>
    <?php endif; ?>

</div>

<!-- ===== MODAL NOVO PROJETO ===== -->
<div id="newProjectModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title">Novo Projeto</h5>
            <button type="button" class="close" onclick="closeNewProjectModal()">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label for="projectName">Nome do Projeto</label>
                <input type="text" id="projectName" placeholder="Ex: Meu Site Profissional">
                <div class="form-help">Escolha um nome descritivo para seu projeto</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-secondary-modal" onclick="closeNewProjectModal()">Cancelar</button>
            <button type="button" class="btn-modal btn-primary-modal" onclick="createNewProject()">Próximo</button>
        </div>
    </div>
</div>

<!-- ===== MODAL SELEÇÃO DE TEMPLATES ===== -->
<div id="templateSelectorModal" class="modal">
    <div class="modal-content modal-templates">
        <div class="modal-header">
            <h5 class="modal-title">Escolha um Template</h5>
            <button type="button" class="close" onclick="closeTemplateSelector()">&times;</button>
        </div>
        <div class="modal-body">
            <p style="color: #666; margin-bottom: 20px;">Selecione um template para começar. Você poderá customizar tudo depois!</p>
            <div id="templatesGrid" class="templates-grid"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-modal btn-secondary-modal" onclick="closeTemplateSelector()">Cancelar</button>
        </div>
    </div>
</div>

<!-- ===== STYLES ADICIONAIS ===== -->
<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .modal.show {
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .modal-content {
        background-color: white;
        width: 100%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .close {
        background: none;
        border: none;
        font-size: 28px;
        font-weight: bold;
        color: #999;
        cursor: pointer;
        padding: 0;
    }

    .close:hover,
    .close:focus {
        color: #333;
    }



/* ===== ESTILOS PARA O MODAL DE TEMPLATES ===== */
.modal-templates .modal-content {
    max-width: 900px;
}

.templates-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 20px;
    max-height: 60vh;
    overflow-y: auto;
    padding: 10px;
}

/* ===== CARD DO TEMPLATE ===== */
.template-card {
    background: white;
    border: 2px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s;
    position: relative;
}

.template-card:hover {
    border-color: #667eea;
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(102, 126, 234, 0.15);
}

/* Destaque especial para templates premium */
.template-card.template-premium {
    border-color: #8b5cf6;
}

.template-card.template-premium:hover {
    border-color: #7c3aed;
    box-shadow: 0 10px 30px rgba(139, 92, 246, 0.25);
}

/* ===== BADGE PREMIUM ===== */
.badge-premium {
    position: absolute;
    top: 12px;
    right: 12px;
    background: linear-gradient(135deg, #a855f7 0%, #7c3aed 100%);
    color: white;
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 11px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.8px;
    z-index: 10;
    display: flex;
    align-items: center;
    gap: 5px;
    box-shadow: 0 4px 15px rgba(168, 85, 247, 0.5);
    animation: pulse-premium 2s ease-in-out infinite;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.badge-premium svg {
    width: 14px;
    height: 14px;
    filter: drop-shadow(0 0 2px rgba(255, 255, 255, 0.8));
    animation: rotate-star 4s linear infinite;
}

/* Animações */
@keyframes pulse-premium {
    0%, 100% {
        box-shadow: 0 4px 15px rgba(168, 85, 247, 0.5);
        transform: scale(1);
    }
    50% {
        box-shadow: 0 6px 25px rgba(168, 85, 247, 0.8);
        transform: scale(1.05);
    }
}

@keyframes rotate-star {
    0% {
        transform: rotate(0deg);
    }
    100% {
        transform: rotate(360deg);
    }
}

/* ===== THUMBNAIL DO TEMPLATE ===== */
.template-thumb {
    width: 100%;
    height: 160px;
    background: #f9fafb;
    overflow: hidden;
    position: relative;
}

.template-thumb img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s;
}

.template-card:hover .template-thumb img {
    transform: scale(1.05);
}

.template-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: #d1d5db;
    background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
}

/* ===== INFO DO TEMPLATE ===== */
.template-info {
    padding: 15px;
}

.template-info h6 {
    margin: 0 0 8px 0;
    font-size: 16px;
    font-weight: 600;
    color: #1f2937;
    display: flex;
    align-items: center;
    gap: 6px;
}

/* Título com cor especial para premium */
.template-card.template-premium .template-info h6 {
    color: #7c3aed;
}

.template-info p {
    margin: 0;
    font-size: 13px;
    color: #6b7280;
    line-height: 1.5;
}

/* ===== RESPONSIVIDADE ===== */
@media (max-width: 768px) {
    .templates-grid {
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }
    
    .template-thumb {
        height: 140px;
    }
    
    .badge-premium {
        font-size: 10px;
        padding: 5px 10px;
        top: 8px;
        right: 8px;
    }
}

@media (max-width: 480px) {
    .templates-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
    const currentPlanName = '<?= $planData['name'] ?? 'Gratuito' ?>';

    // Funções do Modal
    function showNewProjectModal() {
        document.getElementById('newProjectModal').classList.add('show');
    }

    function closeNewProjectModal() {
        document.getElementById('newProjectModal').classList.remove('show');
        document.getElementById('projectName').value = '';
    }

    let currentProjectId = null;
    let currentProjectName = null;

    // ===== FUNÇÃO CORRIGIDA =====
    function createNewProject() {
        const projectName = document.getElementById('projectName').value;

        if (!projectName.trim()) {
            alert('Por favor, digite um nome para o projeto');
            return;
        }

        currentProjectName = projectName;
        closeNewProjectModal();
        
        // IR DIRETO PARA O SELETOR DE TEMPLATES (não cria o projeto ainda)
        showTemplateSelector();
    }

function showTemplateSelector() {
    // Carregar templates
    fetch('/api/projects/templates')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const modal = document.getElementById('templateSelectorModal');
                const container = document.getElementById('templatesGrid');
                container.innerHTML = '';

                if (data.templates.length === 0) {
                    container.innerHTML = '<p style="text-align: center; color: #999;">Nenhum template disponível</p>';
                } else {
                    data.templates.forEach(template => {
                        const isPremium = template.is_premium == 1;
                        
                        // Badge Premium com ícone
                        const premiumBadge = isPremium 
                            ? `<span class="badge-premium">
                                 <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor">
                                   <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                 </svg>
                                 Premium
                               </span>` 
                            : '';
                        
                        const templateCard = `
                            <div class="template-card ${isPremium ? 'template-premium' : ''}" onclick="selectTemplate(${template.id}, '${template.name}')">
                                ${premiumBadge}
                                <div class="template-thumb">
                                    ${template.thumb_file 
                                        ? `<img src="/templates/thumbs/${template.thumb_file}" alt="${template.name}">` 
                                        : `<div class="template-placeholder">
                                             <i class="fas fa-image"></i>
                                           </div>`
                                    }
                                </div>
                                <div class="template-info">
                                    <h6>${template.title || template.name}</h6>
                                    <p>${template.description || 'Template profissional'}</p>
                                </div>
                            </div>
                        `;
                        container.innerHTML += templateCard;
                    });
                }

                modal.classList.add('show');
            } else {
                alert('Erro ao carregar templates: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            alert('Erro ao carregar templates');
        });
}

    // ===== FUNÇÃO CORRIGIDA - AGORA CRIA O PROJETO COM O TEMPLATE =====
    function selectTemplate(templateId, templateName) {
        console.log('Selecionando template:', templateId, templateName);
        
        // Criar o projeto COM o template_id E carregar o HTML
        fetch('/api/projects/save', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'name=' + encodeURIComponent(currentProjectName) + '&template_id=' + templateId + '&load_template_content=1'
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    console.log('Projeto criado:', data.project_id);
                    // Ir para o editor COM o template
                    window.location.href = '/editor?id=' + data.project_id + '&template=' + templateId;
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro ao criar projeto');
            });
    }

    function closeTemplateSelector() {
        document.getElementById('templateSelectorModal').classList.remove('show');
        currentProjectId = null;
        currentProjectName = null;
    }

    function editProject(id, templateId) {
        if (templateId) {
            window.location.href = '/editor?id=' + id + '&template=' + templateId;
        } else {
            window.location.href = '/editor?id=' + id;
        }
    }

    function deleteProject(id) {
        if (!confirm('Tem certeza que deseja deletar este projeto?')) return;

        fetch('/api/projects/delete?id=' + id, { method: 'POST' })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }

    // Carregar planos
    function loadPlans() {
        fetch('/api/projects/plans')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const container = document.getElementById('plansContainer');
                    container.innerHTML = '';

                    data.plans.forEach(plan => {
                        const isCurrentPlan = plan.name === currentPlanName;
                        const planHTML = `
                        <div class="plan-card ${isCurrentPlan ? 'current' : ''}" data-display-order="${plan.display_order}">
                            ${isCurrentPlan ? '<div class="current-badge">Plano Atual</div>' : ''}
                            <div class="plan-name-header">${plan.name}</div>
                            <div class="plan-price">R$ ${parseFloat(plan.price).toFixed(2)}</div>
                            <div class="plan-price-period">por mês</div>
                            <ul class="plan-features">
                                <li><i class="fas fa-check"></i> ${plan.max_projects} Projetos</li>
                                <li><i class="fas fa-check"></i> ${plan.can_access_premium ? 'Acesso a Templates Premium' : 'Sem Acesso a Templates Premium'}</li>
                                <li><i class="fas fa-check"></i> ${plan.max_domains} Domínios próprios</li>
                            </ul>
                            <button class="upgrade-button ${isCurrentPlan ? 'current' : ''}"
                                onclick="${isCurrentPlan ? 'return false;' : 'upgradePlan(' + plan.id + ')'}"
                                ${isCurrentPlan ? 'disabled' : ''}>
                                ${isCurrentPlan ? 'Plano Atual' : 'Fazer Upgrade'}
                            </button>
                        </div>
                    `;
                        container.innerHTML += planHTML;
                    });
                    disableLowerPlans();
                }
            });
    }

    function upgradePlan(planId) {
        if (!confirm('Deseja fazer upgrade para este plano?')) return;

        fetch('/api/projects/upgrade', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'plan_id=' + planId
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Upgrade realizado com sucesso!');
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            });
    }

    window.addEventListener('load', loadPlans);

    window.onclick = function(event) {
        const modal = document.getElementById('newProjectModal');
        if (event.target == modal) {
            modal.classList.remove('show');
        }
    }

    function disableLowerPlans() {
        const currentPlanName = '<?= $planData['name'] ?? 'Gratuito' ?>';
        let currentPlanOrder = null;
        
        document.querySelectorAll('.plan-card').forEach(card => {
            const planName = card.querySelector('.plan-name-header')?.textContent.trim();
            if (planName === currentPlanName) {
                currentPlanOrder = parseInt(card.getAttribute('data-display-order')) || 0;
            }
        });

        if (currentPlanOrder === null) {
            console.warn('Plano atual não encontrado:', currentPlanName);
            return;
        }

        document.querySelectorAll('.upgrade-button').forEach(btn => {
            const planCard = btn.closest('.plan-card');
            const planDisplayOrder = parseInt(planCard.getAttribute('data-display-order')) || 0;

            if (planDisplayOrder <= currentPlanOrder) {
                btn.disabled = true;
                btn.style.cursor = 'not-allowed';
                btn.style.opacity = '0.5';
                btn.textContent = planDisplayOrder === currentPlanOrder ? 'Plano Atual' : 'Chame no whatsapp';
            }
        });
    }
</script>

</body>

</html>