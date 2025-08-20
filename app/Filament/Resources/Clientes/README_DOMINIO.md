# Documentação dos principais arquivos do domínio

## Modelos

### app/Models/Cliente.php
**Descrição:**
Representa a empresa/cliente do sistema. Contém dados cadastrais, documentos, contatos, endereços e informações de uso do sistema.
- **Campos principais:** nome, contrato, status, tipo_pessoa, cpf_cnpj, razao_social, nome_fantasia, contatos, endereços, etc.
- **Regras:**
  - Guarda CPF/CNPJ apenas com dígitos.
  - Gera contrato automaticamente se não informado.
  - Sempre preenche o campo 'nome' (NOT NULL).
  - Possui relacionamentos com endereços, pessoas e contabilidades.
- **Acessores:**
  - getCpfCnpjFormatadoAttribute: retorna CPF/CNPJ formatado para exibição.

### app/Models/Pessoa.php
**Descrição:**
Representa um contato/pessoa associada a uma empresa (cliente).
- **Campos principais:** empresa_id, tipo, nome, cargo, cpf, email, telefone, celular, principal, observacoes.
- **Regras:**
  - Guarda CPF apenas com dígitos.
  - Se marcada como principal, desmarca as outras pessoas do mesmo cliente.
- **Relacionamento:** pertence a Cliente.

### app/Models/Endereco.php
**Descrição:**
Representa um endereço vinculado a uma empresa (cliente).
- **Campos principais:** empresa_id, tipo, rotulo, cep, rua, numero, complemento, bairro, cidade, uf, padrao.
- **Regras:**
  - Guarda CEP apenas com dígitos.
  - UF sempre em maiúsculo.
  - Se marcado como padrão, desmarca os outros endereços do mesmo cliente.
- **Relacionamento:** pertence a Cliente.

### app/Models/Contabilidade.php
**Descrição:**
Representa o escritório de contabilidade associado ao cliente.
- **Campos principais:** empresa_id, razao_social, cnpj, nome_contato, email, telefone, principal, observacoes, user_id.
- **Regras:**
  - Guarda CNPJ e telefone apenas com dígitos.
  - Se marcada como principal, desmarca as outras contabilidades do mesmo cliente.
- **Acessores:**
  - getCnpjFormatadoAttribute: retorna CNPJ formatado.
  - getTelefoneFormatadoAttribute: retorna telefone formatado.
- **Relacionamento:** pertence a Cliente.


## RelationManagers Filament

### app/Filament/Resources/Clientes/RelationManagers/PessoasRelationManager.php
**Descrição:**
Gerencia o CRUD de pessoas/contatos do cliente no painel Filament.
- **Formulário:**
  - Campos com feedback visual (CPF, e-mail, telefone, celular).
  - Validação e máscaras para documentos e contatos.
  - Toggle para marcar como principal.
- **Tabela:**
  - Exibe nome, tipo, cargo, e-mail, celular, principal (badge colorido).
  - Ação para definir como principal (só um por cliente).

### app/Filament/Resources/Clientes/RelationManagers/EnderecosRelationManager.php
**Descrição:**
Gerencia o CRUD de endereços do cliente no painel Filament.
- **Formulário:**
  - Campos com feedback visual para CEP.
  - Busca automática de dados via CEP.
  - Toggle para marcar como padrão.
- **Tabela:**
  - Exibe rótulo, tipo, rua, número, bairro, cidade, UF, CEP, padrão (badge colorido).
  - Ação para definir como padrão (só um por cliente).

### app/Filament/Resources/Clientes/RelationManagers/ContabilidadeRelationManager.php
**Descrição:**
Gerencia o CRUD de escritórios de contabilidade do cliente no painel Filament.
- **Formulário:**
  - Campos com feedback visual (CNPJ, e-mail, telefone).
  - Validação e máscaras para documentos e contatos.
  - Toggle para marcar como principal.
- **Tabela:**
  - Exibe nome do responsável, e-mail, telefone, nome do escritório, CNPJ, principal (badge colorido).
  - Ação para definir como principal (só um por cliente).


---

> Para dúvidas sobre regras de negócio, validações ou integrações, consulte os comentários nos próprios arquivos ou entre em contato com o responsável pelo projeto.
