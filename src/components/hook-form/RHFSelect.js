import { forwardRef } from 'react';
import PropTypes from 'prop-types';
import { FormControl, InputLabel, Select, MenuItem } from '@mui/material';
import { useController } from 'react-hook-form';

const RHFSelect = forwardRef(({ name, label, children, helperText, ...other }, ref) => {
  const { field, fieldState: { error } } = useController({ name });

  return (
    <FormControl fullWidth error={!!error}>
      <InputLabel>{label}</InputLabel>
      <Select
        {...field}
        label={label}
        {...other}
      >
        {children}
      </Select>
    </FormControl>
  );
});

RHFSelect.propTypes = {
  children: PropTypes.node,
  helperText: PropTypes.string,
  label: PropTypes.string,
  name: PropTypes.string,
};

export default RHFSelect; 