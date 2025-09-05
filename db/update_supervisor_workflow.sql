-- Add supervisor_id and role to tblemployees
ALTER TABLE tblemployees ADD COLUMN supervisor_id INT DEFAULT NULL, ADD COLUMN role ENUM('Employee','Supervisor','Admin') DEFAULT 'Employee';

-- Add supervisor approval fields to tblleaves
ALTER TABLE tblleaves 
  ADD COLUMN SupervisorRemark TEXT DEFAULT NULL,
  ADD COLUMN SupervisorStatus ENUM('Pending','Approved','Declined') DEFAULT 'Pending',
  ADD COLUMN SupervisorActionDate DATETIME DEFAULT NULL;

-- (Optional) Add foreign key constraint if you want referential integrity
ALTER TABLE tblemployees ADD CONSTRAINT fk_supervisor FOREIGN KEY (supervisor_id) REFERENCES tblemployees(id);
