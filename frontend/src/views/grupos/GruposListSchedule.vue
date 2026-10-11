<template>
  <div>
    <div class="module-header">
      <div class="header-info">
        <div class="header-icon"><i class="fas fa-users"></i></div>
        <div>
          <h1 class="header-title">Grupos</h1>
          <p class="header-subtitle" v-if="hasSearched">{{ items.length }} de {{ totalItems }} grupos</p>
          <p class="header-subtitle" v-else>Gestión de grupos</p>
        </div>
      </div>
      <button class="btn-create" v-if="hasSearched" @click="openCreateModal"><i class="fas fa-plus"></i><span>Nuevo Grupo</span></button>
    </div>

    <!-- ... rest of existing template until the schedule button ... -->
  </div>
</template>
