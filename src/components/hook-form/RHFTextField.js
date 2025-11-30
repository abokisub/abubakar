import { forwardRef } from 'react';
import PropTypes from 'prop-types';
import { TextField } from '@mui/material';
import { useController } from 'react-hook-form';

const RHFTextField = forwardRef(({ name, helperText, type, ...other }, ref) => {
  const { field, fieldState: { error } } = useController({ name });

  return (
    <TextField
      {...field}
      fullWidth
      type={type}
      value={type === 'number' && field.value === 0 ? '' : field.value}
      error={!!error}
      helperText={error ? error?.message : helperText}
      {...other}
    />
  );
});

RHFTextField.propTypes = {
  helperText: PropTypes.string,
  name: PropTypes.string,
  type: PropTypes.string,
};

export default RHFTextField; 