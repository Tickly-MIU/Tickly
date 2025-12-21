export interface Task {
  task_id: number;
  category_id?: number;
  title: string;
  description?: string;
  priority: 'low' | 'medium' | 'high';
  deadline?: string;
  status: 'pending' | 'completed';
  created_at: string;
  updated_at: string;
}

