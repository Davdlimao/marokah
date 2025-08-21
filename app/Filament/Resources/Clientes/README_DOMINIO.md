# Marokah • Módulo de Clientes (Laravel 12 + Filament)

Este módulo oferece uma solução robusta para a gestão de clientes, endereços, pessoas de contato e contabilidades, utilizando Laravel 12 e Filament. Inclui validações em tempo real, preenchimento automático por CEP/CNPJ e controles de entidade principal/padrão, proporcionando uma experiência de usuário eficiente e segura.

---

## ✨ Principais Funcionalidades

- **Cadastro de Clientes (PF/PJ)**
  - Máscara dinâmica e validação de CPF/CNPJ com feedback visual.
  - Auto-preenchimento de dados via CNPJ utilizando [BrasilAPI].
  - Campos comerciais: e-mail, telefone, WhatsApp.
  - Geração automática de contrato (`MK-AAAA-000001`) com 6 dígitos sequenciais.
  - Campo de perfil do cliente (opcional): Loja / Produtor (não exibido se nulo).

- **Gestão de Endereços**
  - Busca de CEP com validação visual.
  - Preenchimento automático de endereço via tabela local ou fallback BrasilAPI.
  - Ação "Definir como padrão" (um endereço padrão por cliente).
  - Exibição de CEP formatado (`99999-999`).

- **Gestão de Pessoas**
  - Validação de CPF, e-mail, telefone fixo e celular/WhatsApp com feedback visual.
  - Ação "Definir como principal" (uma pessoa principal por cliente).
  - Indicação visual de principal na listagem.

- **Gestão de Contabilidade**
  - Validação de CNPJ, e-mail e telefone.
  - Ação "Definir como principal" (uma contabilidade principal por cliente).
  - Formatação de telefone na listagem.

- **Experiência do Usuário**
  - Feedback visual direto nos campos (ícone + mensagem).
  - Máscaras amigáveis para entrada de dados; persistência apenas de dígitos no banco.

---

## 🧱 Requisitos

- PHP 8.2+
- Composer
- MySQL/MariaDB
- Laravel 12.x
- Filament v3
- Extensões PHP comuns (`pdo_mysql`, `mbstring`, etc.)

---

## 🧭 Estrutura de Pastas

```
app/
 └─ Filament/
     └─ Resources/
         └─ Clientes/
             ├─ ClienteResource.php
             ├─ Pages/
             │   ├─ CreateCliente.php
             │   ├─ EditCliente.php
             │   └─ ListClientes.php
             ├─ RelationManagers/
             │   ├─ EnderecosRelationManager.php
             │   ├─ PessoasRelationManager.php
             │   └─ ContabilidadeRelationManager.php
             └─ Schemas/
                 └─ ClienteForm.php
app/
 ├─ Models/
 │   ├─ Cliente.php           # tabela: `empresas`
 │   ├─ Endereco.php          # tabela: `enderecos`
 │   ├─ Pessoa.php            # tabela: `pessoas`
 │   └─ Contabilidade.php     # tabela: `contabilidades`
 ├─ Services/
 │   └─ CnpjLookup.php        # Integração BrasilAPI
 └─ Support/
     └─ BrDocuments.php       # Validação CPF/CNPJ
```

---

## 🗂️ Esquema de Banco (Resumo)

- **Colação recomendada:** `utf8mb4_unicode_ci`

### Tabelas

#### `empresas` (Model: Cliente)
- `id`, `tipo_pessoa` (PF|PJ), `cpf_cnpj`, `razao_social`, `nome_fantasia` (nullable)
- `email_comercial`, `telefone_comercial`, `celular_comercial`
- `status` (ATIVADO|DESATIVADO|SUSPENSO|BLOQUEADO|CANCELADO)
- `contrato` (auto: MK-YYYY-000001)
- `dia_vencimento` (1..31)
- `perfil` (nullable: loja|produtor)
- `observacoes`, `timestamps`

> **Regra:** O código do contrato é gerado automaticamente em `Cliente::booted()` se não informado.

#### `enderecos`
- `id`, `empresa_id` (FK), `tipo` (principal|cobranca|entrega|outro)
- `rotulo`, `cep` (apenas dígitos), `rua`, `numero`, `complemento`, `referencia`
- `bairro`, `cidade`, `uf`
- `padrao` (bool), `timestamps`

> **Regra:** Ao definir `padrao = true`, os demais endereços do cliente são atualizados para `false`.

#### `pessoas`
- `id`, `empresa_id` (FK), `tipo` (representante|financeiro|compras|fiscal|comercial|suporte|ti|outro)
- `nome`, `cargo` (nullable), `cpf` (apenas dígitos/nulo)
- `email`, `telefone` (apenas dígitos/nulo), `celular` (apenas dígitos/nulo)
- `principal` (bool), `observacoes` (nullable), `timestamps`

> **Regra:** Ao marcar `principal = true`, as demais pessoas do cliente são definidas como `false`.

#### `contabilidades`
- `id`, `empresa_id` (FK)
- `razao_social` (nullable), `cnpj` (apenas dígitos/nulo)
- `nome_contato`, `email`, `telefone` (apenas dígitos/nulo)
- `principal` (bool), `ordem` (int, opcional), `observacoes` (nullable), `timestamps`

> **Regra:** Ao marcar `principal = true`, as demais contabilidades do cliente são atualizadas para `false`.

#### Chaves Estrangeiras

Garanta as FKs para integridade referencial:

```sql
ALTER TABLE enderecos
  ADD CONSTRAINT fk_enderecos_empresas
  FOREIGN KEY (empresa_id) REFERENCES empresas(id)
  ON DELETE CASCADE;

ALTER TABLE pessoas
  ADD CONSTRAINT fk_pessoas_empresas
  FOREIGN KEY (empresa_id) REFERENCES empresas(id)
  ON DELETE CASCADE;

ALTER TABLE contabilidades
  ADD CONSTRAINT fk_contabilidades_empresas
  FOREIGN KEY (empresa_id) REFERENCES empresas(id)
  ON DELETE CASCADE;
```

> Se necessário, ajuste os dados antes de criar as FKs ou utilize sem `ON DELETE` e ajuste posteriormente.

---

## 🧩 Detalhamento das Funcionalidades

### 1. Cliente (PF/PJ)
- Campo CPF/CNPJ com máscara dinâmica e validação via `App\Support\BrDocuments`.
- Feedback visual (ícone e mensagem).
- Auto-preenchimento de dados via CNPJ (BrasilAPI): razão social, fantasia, endereço.
- Perfil do cliente: loja | produtor (não exibido se nulo).

### 2. Endereços
- Campo CEP com máscara e validação.
- Busca local e fallback BrasilAPI.
- Feedback visual (ícone e dica).
- Ação "Definir como padrão" (apenas um por cliente).
- Exibição formatada do CEP.

### 3. Pessoas
- Validação de CPF, e-mail, telefone e celular.
- Ação "Definir como principal" (apenas uma por cliente).
- Indicação visual de principal na tabela.

### 4. Contabilidade
- Validação de CNPJ, e-mail e telefone.
- Ação "Definir como principal" (apenas uma por cliente).
- Formatação de telefone na listagem.

---

## 🔐 Referências & Integridade

- Utilize chaves estrangeiras para garantir integridade referencial e facilitar integrações e exclusões em cascata.
- Caso o banco de dados não permita a criação imediata das FKs, realize a limpeza dos dados ou ajuste as constraints posteriormente.

---

## 🩺 Troubleshooting

- **"Field 'nome' doesn’t have a default value":** Verifique o atributo `fillable` do modelo Cliente e a correspondência dos campos do formulário.
- **"Unknown column 'padrao' in order clause":** Use `->defaultSort('principal', 'desc')` para Pessoas/Contabilidade; `padrao` é exclusivo de Endereços.
- **Validações com `$attribute` indisponível:** Em Filament v3, utilize closures em `->rules()` para evitar erros de resolução de container.
- **Telefone exibindo "-":** Indica valor nulo ou quantidade de dígitos inválida (diferente de 10/11).

---

## 🧰 Convenções

- Persistência apenas de dígitos para CPF/CNPJ/telefones/CEP.
- Máscaras aplicadas apenas na interface.
- Apenas um registro principal/padrão por cliente (endereços, pessoas, contabilidade).
- Feedback visual consistente (ícone e mensagem).
- Fallback para BrasilAPI sem chave, com timeouts curtos e silenciosos.

---

## 📄 Licença

Projeto interno Marokah. Todos os direitos reservados.

---

## Créditos

- [FilamentPHP](https://filamentphp.com/)
- [BrasilAPI](https://brasilapi.com.br/)
- Equipe Marokah — Módulo de Clientes
