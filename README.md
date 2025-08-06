# FastrackGPS - Sistema de Rastreamento GPS

[![PHP Version](https://img.shields.io/badge/php-%3E%3D8.1-777BB4?logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/license-Proprietary-red.svg)](LICENSE)

FastrackGPS é um sistema completo de rastreamento GPS para gestão de frotas e veículos, oferecendo monitoramento em tempo real, cercas virtuais, comandos remotos e relatórios detalhados.

## 🏗️ Arquitetura do Sistema

O sistema foi reorganizado em uma arquitetura modular com três componentes principais:

### 1. Sistema Legacy (`/legacy-fastrackgps/`)
- **Tecnologia**: PHP clássico com funções mysql_* (PHP < 7.0)
- **Status**: Sistema original funcional, ainda pode estar em produção
- **Características**:
  - Interface baseada em framesets HTML
  - Conexões diretas ao MySQL
  - Sem autoloader (require/include manual)
  - Ponto de entrada: `index.php`, painéis admin em `/administracao/`

### 2. Sistema Moderno (`/modern-fastrackgps/`)
- **Tecnologia**: PHP 8.1+ com Domain-Driven Design
- **Arquitetura**: Clean Architecture com separação rígida de responsabilidades
- **Características**:
  - PSR-4 autoloading com Composer
  - Práticas modernas de segurança
  - Type safety com PHP 8.1+ strict types
  - Twig templates, Doctrine DBAL
  - Testes automatizados com PHPUnit

### 3. Módulos Compartilhados (`/shared-modules/`)
- **Propósito**: Configurações, assets e utilitários comuns
- **Funcionalidade**: 
  - Bridge entre sistemas legacy e moderno via symlinks
  - Configurações de banco, JavaScript, CSS e imagens
  - Scripts de migração e validação

## 🚀 Instalação e Configuração

### Pré-requisitos

#### Sistema Moderno
- PHP 8.1 ou superior
- MySQL 5.7+ ou 8.0+
- Composer 2.0+
- Extensões: pdo, json, curl

#### Sistema Legacy
- PHP 5.6 - 7.4 (compatibilidade com funções mysql_*)
- MySQL 5.6+
- Apache/Nginx com mod_rewrite

### Instalação do Sistema Moderno

```bash
cd modern-fastrackgps/

# Instalar dependências
composer install

# Configurar ambiente
cp .env.example .env
# Editar .env com suas configurações

# Executar testes
composer test

# Verificar qualidade do código
composer run quality
```

### Instalação do Sistema Legacy

```bash
# Configurar banco de dados
cp shared-modules/config/database.php.example shared-modules/config/database.php

# Configurar permissões
chmod -R 755 legacy-fastrackgps/
chmod -R 777 legacy-fastrackgps/logs/ # se existir

# Importar banco de dados
mysql -u root -p tracker2 < shared-modules/database/schema.sql
```

## 📊 Banco de Dados

### Schema Principal
- **Nome padrão**: `tracker2` ou `fastrackgps`
- **Tabelas principais**:
  - `vehicles` - Gestão de veículos
  - `positions` - Posições GPS
  - `alerts` - Sistema de alertas
  - `users` - Usuários do sistema
  - `geofences` - Cercas virtuais
  - `commands` - Comandos enviados aos dispositivos

### Configuração
```php
// shared-modules/config/database.php
return [
    'host' => 'localhost',
    'database' => 'tracker2',
    'username' => 'your_user',
    'password' => 'your_password',
    'charset' => 'utf8mb4'
];
```

## 🏛️ Estrutura do Sistema Moderno

```
modern-fastrackgps/
├── src/
│   ├── Auth/          # Autenticação e autorização
│   ├── Vehicle/       # Gestão de veículos (domínio)
│   ├── Tracking/      # Rastreamento GPS e posições
│   ├── Alert/         # Sistema de alertas
│   ├── Geofence/      # Gestão de cercas virtuais
│   ├── Command/       # Comandos para dispositivos GPS
│   ├── Payment/       # Processamento de pagamentos
│   ├── Core/          # Infraestrutura compartilhada
│   └── Security/      # Utilitários de segurança
├── tests/             # Testes automatizados
├── templates/         # Templates Twig
├── public/            # Assets públicos
└── storage/           # Logs e cache
```

### Padrões DDD Implementados

Cada domínio segue os padrões Domain-Driven Design:

- **Entity/**: Entidades de domínio
- **Repository/**: Interfaces e implementações de acesso a dados
- **Service/**: Lógica de negócio
- **Controller/**: Manipuladores HTTP
- **ValueObject/**: Tipos de valor imutáveis

## 🔧 Comandos de Desenvolvimento

### Sistema Moderno

```bash
# Testes
composer test                    # Executar testes
composer run test-coverage       # Gerar relatório de cobertura

# Qualidade de código
composer run phpstan            # Análise estática (Nível 8)
composer run cs-fix             # Corrigir estilo de código (PSR-12)
composer run cs-check           # Verificar estilo de código
composer run rector             # Refatoração automatizada
composer run quality            # Executar todas as verificações

# Workflow de desenvolvimento
composer run test && composer run phpstan && composer run cs-check
```

### Sistema Legacy
- **Sem processo de build** - Execução direta do PHP
- **Includes manuais** - Declarações require/include tradicionais
- **Conexão direta** - Via `mysql.php` (redirecionado para config compartilhado)

## 🔐 Segurança

### Sistema Moderno
- **Proteção CSRF**: Via `CsrfTokenManager`
- **Sanitização de entrada**: Via `InputSanitizer`
- **Prepared statements**: Exclusivamente
- **Type safety**: Com PHP 8.1 strict types
- **Segurança de sessão**: Com configurações personalizáveis

### Sistema Legacy
- **⚠️ Avisos de Segurança**:
  - Contém vulnerabilidades conhecidas
  - Usa funções MySQL deprecated
  - Validação mínima de entrada
  - **NÃO ESTENDER código legacy** - migrar para sistema moderno

## 🧪 Testes

### Sistema Moderno
- **PHPUnit 10** com configuração rigorosa
- **Testes unitários**: `tests/Unit/` para componentes isolados
- **Testes de integração**: `tests/Integration/` para interações de banco
- **Meta de cobertura**: >90% (exclui código Legacy)
- **Banco de testes**: SQLite em memória para velocidade

### Sistema Legacy
- **Sem testes automatizados** atualmente
- **Testes manuais** necessários
- **Banco**: Geralmente MySQL schema `tracker2`

## 📈 Funcionalidades Principais

### 🚗 Gestão de Veículos
- Cadastro, edição e exclusão de veículos
- Atribuição a usuários
- Configuração de dispositivos GPS
- Histórico de manutenção

### 📍 Rastreamento em Tempo Real
- Posições GPS ao vivo no Google Maps
- Atualização automática via AJAX
- Status do sinal GPS
- Velocidade e direção

### 🔒 Cercas Virtuais (Geofencing)
- Criação de zonas virtuais
- Alertas de entrada/saída
- Múltiplos tipos de cerca (circular, poligonal)
- Horários de funcionamento

### 📱 Sistema de Comandos
- Envio de comandos para dispositivos GPS
- Bloqueio/desbloqueio remoto
- Configuração de parâmetros
- Histórico de comandos

### 📊 Relatórios e Histórico
- Reprodução de rotas históricas
- Relatórios de velocidade
- Análise de paradas
- Exportação de dados

### 🚨 Sistema de Alertas
- Alertas de velocidade
- Alertas de geofence
- Alertas de bateria baixa
- Alertas de pânico
- Notificações por email/SMS

### 💰 Gestão de Pagamentos
- Controle de mensalidades
- Status de pagamento
- Histórico financeiro
- Bloqueio por inadimplência

## 🔄 Migração Legacy → Moderno

### Processo de Migração

1. **Análise de Dados**
   ```bash
   php shared-modules/scripts/analyze-legacy-data.php
   ```

2. **Backup do Sistema Legacy**
   ```bash
   php shared-modules/scripts/backup-legacy-system.php
   ```

3. **Migração de Dados**
   ```bash
   php shared-modules/scripts/migrate-data.php
   ```

4. **Validação de Migração**
   ```bash
   php shared-modules/scripts/validate-migration.php
   ```

### Compatibilidade Durante Transição

O sistema permite execução paralela durante a migração:
- Assets compartilhados via symlinks
- Configuração de banco unificada
- Scripts de sincronização de dados

## 🌐 Integração com APIs

### Google Maps API
- Exibição de mapas interativos
- Geocodificação reversa
- Cálculo de rotas
- KML personalizado

### WebServices
- **Endpoints disponíveis**: `/webservices/`
- **Autenticação**: Token-based
- **Formatos**: JSON, XML
- **Documentação**: Swagger (sistema moderno)

## 📱 Suporte Mobile

### Sistema Legacy
- Interface responsiva básica
- Assets otimizados em `/imagens/mobile/`

### Sistema Moderno
- Progressive Web App (PWA)
- API REST completa
- Templates mobile-first

## 🔧 Troubleshooting

### Problemas Comuns

#### Erro de Conexão com Banco
```bash
# Verificar configuração
php shared-modules/scripts/test-database-connection.php

# Verificar logs
tail -f shared-modules/logs/database.log
```

#### Problemas de Permissão
```bash
# Corrigir permissões
chmod -R 755 .
chmod -R 777 storage/ logs/
```

#### Assets não carregam
```bash
# Recriar symlinks
php shared-modules/scripts/create-symlinks.php
```

## 📝 Logs

### Localização dos Logs
- **Sistema Moderno**: `modern-fastrackgps/storage/logs/`
- **Sistema Legacy**: `shared-modules/logs/`
- **Banco de dados**: `shared-modules/logs/database.log`

### Configuração de Log
```php
// modern-fastrackgps/.env
LOG_LEVEL=debug
LOG_CHANNEL=single
```

## 🤝 Contribuição

### Workflow de Desenvolvimento

#### Para Novas Funcionalidades
1. **Sempre usar sistema moderno** (`/modern-fastrackgps/`)
2. Seguir padrões DDD e estrutura existente
3. Escrever testes para nova funcionalidade
4. Executar verificações de qualidade antes do commit

#### Para Manutenção do Legacy
1. **Apenas alterações mínimas** - corrigir bugs críticos
2. **Criar backups** antes de qualquer alteração
3. Considerar caminho de migração para sistema moderno
4. Testar completamente (sem testes automatizados)

### Atualização de Assets
1. Adicionar novos assets em `shared-modules/assets/`
2. Ambos sistemas acessarão via symlinks automaticamente
3. Atualizar referências em ambos sistemas se necessário

## 🚀 Deploy e Produção

### Sistema Moderno (Recomendado)

```bash
# Ambiente de produção
composer install --no-dev --optimize-autoloader

# Cache e otimizações
php bin/console cache:clear --env=prod
php bin/console cache:warm --env=prod

# Configurar web server
# Apontar document root para modern-fastrackgps/public/
```

### Sistema Legacy

```bash
# Configurar virtual host
# Apontar document root para legacy-fastrackgps/

# Configurar PHP (php.ini)
error_reporting = E_ALL & ~E_DEPRECATED
display_errors = Off
log_errors = On
```

## 📋 Checklist de Deploy

- [ ] Configurações de ambiente (.env)
- [ ] Permissões de diretório
- [ ] Configuração de banco de dados
- [ ] API keys (Google Maps)
- [ ] Certificados SSL
- [ ] Backup de dados
- [ ] Testes em ambiente staging
- [ ] Monitoramento configurado

## 📞 Suporte

### Documentação Adicional
- [Guia de Instalação](shared-modules/docs/INSTALLATION_GUIDE.md)
- [Relatório de Refatoração](shared-modules/docs/REFACTORING_REPORT.md)
- [Documentação da API](shared-modules/docs/API_DOCUMENTATION.md)

### Versioning
- **Sistema Legacy**: v1.x (manutenção apenas)
- **Sistema Moderno**: v2.x (desenvolvimento ativo)

### Performance
- **Sistema Legacy**: Queries diretas, cache mínimo
- **Sistema Moderno**: Query builder com potencial para camadas de cache
- **Assets compartilhados**: Servidos diretamente, sem processo de build

### Considerações de Performance
- **Connection pooling** para deployments de alto tráfego
- **CDN** para assets estáticos
- **Índices de banco** otimizados para consultas frequentes

---

## 🎯 Roadmap

### Próximas Funcionalidades
- [ ] API GraphQL
- [ ] Microserviços
- [ ] Containerização (Docker)
- [ ] Integração com IoT
- [ ] Machine Learning para análise de rotas
- [ ] Aplicativo mobile nativo

### Migração Completa
- [ ] Migração de todos os usuários para sistema moderno
- [ ] Descontinuação do sistema legacy
- [ ] Otimização de performance
- [ ] Implementação de cache distribuído

---

**FastrackGPS** - Sistema de Rastreamento GPS Profissional  
Desenvolvido com ❤️ para gestão eficiente de frotas