export interface Task {
  task_id: number;
  user_id: number;
  category_id?: number;
  title: string;
  description?: string;
  priority: 'low' | 'medium' | 'high';
  deadline?: Date;
  status: 'pending' | 'completed';
  created_at: Date;
  updated_at: Date;
}

export interface ITaskForm {
  user_id: number;
  category_id?: number;
  title: string;
  description?: string;
  priority: 'low' | 'medium' | 'high';
  deadline?: Date;
  status: 'pending' | 'completed';
  created_at: Date;
  updated_at?: Date;
}