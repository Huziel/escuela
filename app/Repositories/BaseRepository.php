<?php namespace App\Repositories;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

abstract class BaseRepository
{
    protected Model $model;
    
    public function __construct()
    {
        $this->model = app($this->getModelClass());
    }
    
    abstract protected function getModelClass(): string;
    
    public function all(array $columns = ['*']): Collection
    {
        return $this->model->all($columns);
    }
    
    public function paginate(int $perPage = 15, array $columns = ['*'], array $relations = []): LengthAwarePaginator
    {
        return $this->model->with($relations)->paginate($perPage, $columns);
    }
    
    public function find(int $id, array $relations = []): ?Model
    {
        return $this->model->with($relations)->find($id);
    }
    
    public function findOrFail(int $id, array $relations = []): Model
    {
        return $this->model->with($relations)->findOrFail($id);
    }
    
    public function create(array $data): Model
    {
        return $this->model->create($data);
    }
    
    public function update(int $id, array $data): Model
    {
        $record = $this->findOrFail($id);
        $record->update($data);
        return $record->fresh();
    }
    
    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }
    
    public function where(string $column, $value): Collection
    {
        return $this->model->where($column, $value)->get();
    }
    
    public function count(): int
    {
        return $this->model->count();
    }
}
