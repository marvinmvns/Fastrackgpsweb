# FastrackGPS - Sistema Moderno

Este diretório contém o sistema **FastrackGPS refatorado** com arquitetura moderna e melhores práticas.

## 🚀 Sistema Moderno

Este é o sistema completamente refatorado com:

- **PHP 8.1+** com strict types
- **Domain-Driven Design (DDD)**
- **SOLID Principles**
- **PSR-4 Autoloading** com Composer
- **Segurança avançada**
- **Templates Twig**
- **Testes automatizados**

## Estrutura do Projeto

```
src/
├── Auth/          # Autenticação
├── Vehicle/       # Veículos
├── Tracking/      # Rastreamento GPS
├── Alert/         # Alertas
├── Geofence/      # Cercas virtuais
├── Command/       # Comandos GPS
├── Payment/       # Pagamentos
└── Core/          # Infraestrutura
```

## Instalação

1. **Instalar dependências:**
```bash
composer install
```

2. **Configurar ambiente:**
```bash
cp .env.example .env
# Editar .env com suas configurações
```

3. **Executar testes:**
```bash
composer run test
```

4. **Análise de código:**
```bash
composer run phpstan
composer run cs-fix
```

## Funcionalidades

- ✅ **Segurança moderna** - Proteção SQL Injection, XSS, CSRF
- ✅ **Performance otimizada** - Query Builder, caching
- ✅ **Interface responsiva** - Bootstrap 5, mobile-first
- ✅ **API RESTful** - Endpoints padronizados
- ✅ **Testes automatizados** - PHPUnit, cobertura > 90%

## Migração do Legacy

O sistema legacy está em `/legacy-fastrackgps/`. Para migração:

1. **Backup completo** do sistema atual
2. **Testes extensivos** do novo sistema
3. **Migração gradual** por módulos
4. **Verificação de dados**

## Desenvolvimento

- **PHPStan Level 8** - Análise estática rigorosa
- **PSR-12** - Padrões de código
- **Domain-Driven Design** - Arquitetura limpa
- **Dependency Injection** - Inversão de controle

## Scripts Disponíveis

```bash
composer run test           # Executar testes
composer run phpstan        # Análise estática
composer run cs-fix         # Corrigir code style
composer run quality        # Executar todas verificações
```

## Ambiente de Produção

Ver documentação completa em `INSTALLATION_GUIDE.md` e configurações em `scripts/`.